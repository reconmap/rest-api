<?php declare(strict_types=1);

namespace Reconmap\Tasks;

use Monolog\Logger;
use Reconmap\Processors\ProcessorFactory;
use Reconmap\Repositories\TargetRepository;
use Reconmap\Repositories\TaskRepository;
use Reconmap\Repositories\VulnerabilityRepository;
use Reconmap\Services\ApplicationConfig;
use Reconmap\Services\RedisServer;

class TaskResultProcessor implements ItemProcessor
{
    public function __construct(
        private ApplicationConfig $config,
        private Logger $logger,
        private \mysqli $db,
        private RedisServer $redis,
        private VulnerabilityRepository $vulnerabilityRepository,
        private TaskRepository $taskRepository,
        private TargetRepository $targetRepository,
        private ProcessorFactory $processorFactory)
    {
    }

    public function process(object $item): void
    {
        $path = $item->filePath;

        $task = $this->taskRepository->findById($item->taskId);

        $processor = $this->processorFactory->createByCommandShortName($task['command_short_name']);
        if ($processor) {
            $vulnerabilities = $processor->parseVulnerabilities($path);
            $numVulnerabilities = count($vulnerabilities);
            $this->logger->debug("Number of vulnerabilities in uploaded file: " . $numVulnerabilities);

            foreach ($vulnerabilities as $vulnerability) {
                $vulnerability->project_id = $task['project_id'];
                if (empty($vulnerability->risk)) {
                    $vulnerability->risk = 'medium';
                }
                $vulnerability->creator_uid = $item->userId;

                $targetId = null;
                if (!empty($vulnerability->host)) {
                    $target = $this->targetRepository->findByProjectIdAndName($task['project_id'], $vulnerability->host->name);
                    if ($target) {
                        $this->logger->debug("Host found: " . $target->id);
                        $targetId = $target->id;
                    } else {
                        $targetId = $this->targetRepository->insert($task['project_id'], $vulnerability->host->name, 'hostname');
                        $this->logger->debug("Host created: " . $targetId);
                    }
                }

                $vulnerability->target_id = $targetId;

                try {
                    $this->vulnerabilityRepository->insert($vulnerability);
                } catch (\Exception $e) {
                    $this->logger->error($e->getMessage());
                }
            }
        } else {
            $this->logger->warning("Task type has no processor: ${task['command_short_name']}");
        }

        $this->redis->lPush("notifications:queue",
            json_encode([
                'title' => 'Task results processed.',
                'detail' => date('h:i'),
            ])
        );
    }
}
