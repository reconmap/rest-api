<?php declare(strict_types=1);

namespace Reconmap\Tasks;

use Monolog\Logger;
use Reconmap\Processors\ProcessorFactory;
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

        $targetId = null;
        $vulnerabilityRepository = new VulnerabilityRepository($this->db);

        $taskRepo = new TaskRepository($this->db);
        $task = $taskRepo->findById($item->taskId);

        $processorFactory = new ProcessorFactory;
        $processor = $processorFactory->createByTaskType($task['command_short_name']);
        if ($processor) {
            $vulnerabilities = $processor->parseVulnerabilities($path);
            $this->logger->debug('Vulnerabilities found', $vulnerabilities);

            foreach ($vulnerabilities as $vulnerability) {
                $vulnerability->project_id = $task['project_id'];
                $vulnerability->risk = 'medium';
                $vulnerabilityRepository->insert($item->userId, $vulnerability);
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
