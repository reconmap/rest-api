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
    private ApplicationConfig $config;
    private Logger $logger;
    private \mysqli $db;
    private \Redis $redis;

    public function __construct(ApplicationConfig $config, Logger $logger, \mysqli $db, RedisServer $redis)
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->db = $db;
        $this->redis = $redis;
    }

    public function process(object $item): void
    {
        $path = $item->filePath;

        $vulnerabilityRepository = new VulnerabilityRepository($this->db);

        $taskRepo = new TaskRepository($this->db);
        $task = $taskRepo->findById($item->taskId);

        $processorFactory = new ProcessorFactory;
        $processor = $processorFactory->createByTaskType($task['command_short_name']);
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
                    $targetRepository = new TargetRepository($this->db);
                    $target = $targetRepository->findByProjectIdAndName($task['project_id'], $vulnerability->host->name);
                    if ($target) {
                        $targetId = $target->id;
                    } else {
                        $targetId = $targetRepository->insert($task['project_id'], $vulnerability->host->name, 'hostname');
                    }
                }

                $vulnerability->target_id = $targetId;

                try {
                    $vulnerabilityRepository->insert($vulnerability);
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
