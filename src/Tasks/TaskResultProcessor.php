<?php declare(strict_types=1);

namespace Reconmap\Tasks;

use Monolog\Logger;
use Reconmap\Models\Target;
use Reconmap\Processors\ProcessorFactory;
use Reconmap\Repositories\TargetRepository;
use Reconmap\Repositories\TaskRepository;
use Reconmap\Repositories\VulnerabilityRepository;
use Reconmap\Services\ApplicationConfig;
use Reconmap\Services\RedisServer;

class TaskResultProcessor implements ItemProcessor
{
    public function __construct(
        private ApplicationConfig       $config,
        private Logger                  $logger,
        private \mysqli                 $db,
        private RedisServer             $redis,
        private VulnerabilityRepository $vulnerabilityRepository,
        private TaskRepository          $taskRepository,
        private TargetRepository        $targetRepository,
        private ProcessorFactory        $processorFactory)
    {
    }

    public function process(object $item): void
    {
        $path = $item->filePath;

        $task = $this->taskRepository->findById($item->taskId);
        $commandShortName = $task['command_short_name'];

        $processor = $this->processorFactory->createByCommandShortName($commandShortName);
        if ($processor) {
            $vulnerabilities = $processor->parseVulnerabilities($path);
            $numVulnerabilities = count($vulnerabilities);
            $this->logger->debug("Number of vulnerabilities in uploaded file: " . $numVulnerabilities);

            foreach ($vulnerabilities as $vulnerability) {
                $vulnerability->tags = json_encode([$commandShortName]);
                $vulnerability->project_id = $task['project_id'];
                if (empty($vulnerability->risk)) {
                    $vulnerability->risk = 'medium';
                }
                $vulnerability->creator_uid = $item->userId;

                $targetId = null;
                if (!empty($vulnerability->host)) {
                    $target = $this->targetRepository->findByProjectIdAndName($task['project_id'], $vulnerability->host->name);
                    if ($target) {
                        $targetId = $target->id;
                    } else {
                        $target = new Target();
                        $target->projectId = $task['project_id'];
                        $target->name = $vulnerability->host->name;
                        $target->kind = 'hostname';

                        $targetId = $this->targetRepository->insert($target);
                        $this->logger->debug("New target added: " . $target->name);
                    }
                }

                $vulnerability->target_id = $targetId;

                try {
                    $this->vulnerabilityRepository->insert($vulnerability);
                } catch (\Exception $e) {
                    $this->logger->error($e->getMessage());
                }
            }

            $this->redis->lPush("notifications:queue",
                json_encode([
                    'time' => date('H:i'),
                    'title' => "$numVulnerabilities vulnerabilities have been found",
                    'detail' => "(corresponding to '$commandShortName' results)",
                    'entity' => 'vulnerabilities'
                ])
            );
        } else {
            $this->logger->warning("Task type has no processor: ${task['command_short_name']}");
        }
    }
}
