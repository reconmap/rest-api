<?php declare(strict_types=1);

namespace Reconmap\Tasks;

use Monolog\Logger;
use Reconmap\Models\Target;
use Reconmap\Processors\HostParser;
use Reconmap\Processors\ProcessorFactory;
use Reconmap\Processors\VulnerabilityParser;
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
        $outputParserName = $task['output_parser'];

        $processor = $this->processorFactory->createFromOutputParserName($outputParserName);
        if ($processor) {
            if ($processor instanceof HostParser) {
                $hosts = $processor->parseHost($path);
                foreach ($hosts as $host) {
                    $this->findOrCreateHost($task['project_id'], $host->name, $outputParserName);
                }
                $numHosts = count($hosts);

                $this->redis->lPush("notifications:queue",
                    json_encode([
                        'time' => date('H:i'),
                        'title' => "$numHosts hosts have been found",
                        'detail' => "(corresponding to '$outputParserName' results)",
                        'entity' => 'vulnerabilities'
                    ])
                );
            }
            if ($processor instanceof VulnerabilityParser) {
                $vulnerabilities = $processor->parseVulnerabilities($path);
                $numVulnerabilities = count($vulnerabilities);
                $this->logger->debug("Number of vulnerabilities in uploaded file: " . $numVulnerabilities);

                foreach ($vulnerabilities as $vulnerability) {
                    if (is_array($vulnerability->tags)) {
                        $vulnerability->tags[] = $outputParserName;
                    } else {
                        $vulnerability->tags = [$outputParserName];
                    }
                    $vulnerability->tags = json_encode($vulnerability->tags);
                    $vulnerability->project_id = $task['project_id'];
                    if (empty($vulnerability->risk)) {
                        $vulnerability->risk = 'medium';
                    }
                    $vulnerability->creator_uid = $item->userId;

                    $targetId = null;
                    if (!empty($vulnerability->host)) {
                        $targetId = $this->findOrCreateHost($task['project_id'], $vulnerability->host->name, $outputParserName);
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
                        'detail' => "(corresponding to '$outputParserName' results)",
                        'entity' => 'vulnerabilities'
                    ])
                );
            }
        } else {
            $this->logger->warning("Task type has no processor: ${task['command_short_name']}");
        }
    }

    private function findOrCreateHost(int $projectId, string $hostName, string $parserName): int
    {
        $target = $this->targetRepository->findByProjectIdAndName($projectId, $hostName);
        if ($target) {
            return $target->id;
        }

        $target = new Target();
        $target->tags = json_encode([$parserName]);
        $target->projectId = $projectId;
        $target->name = $hostName;
        $target->kind = 'hostname';

        $targetId = $this->targetRepository->insert($target);
        $this->logger->debug("New target added: " . $target->name);

        return $targetId;
    }
}
