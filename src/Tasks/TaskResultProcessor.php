<?php
declare(strict_types=1);

namespace Reconmap\Tasks;

use Monolog\Logger;
use Reconmap\Processors\ProcessorFactory;
use Reconmap\Repositories\TaskRepository;
use Reconmap\Repositories\TaskResultRepository;
use Reconmap\Repositories\VulnerabilityRepository;
use Reconmap\Services\Config;

class TaskResultProcessor implements ItemProcessor
{
    private Config $config;
    private Logger $logger;
    private \mysqli $db;

    public function __construct(Config $config, Logger $logger, \mysqli $db)
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->db = $db;
    }

    public function process(object $item): void
    {
        $path = $item->filePath;
        $output = file_get_contents($path);

        $repository = new TaskResultRepository($this->db);
        $repository->insert($item->taskId, $item->userId, $output);

        $targetId = null;
        $vulnRepository = new VulnerabilityRepository($this->db);

        $taskRepo = new TaskRepository($this->db);
        $task = $taskRepo->findById($item->taskId);

        $processorFactory = new ProcessorFactory;
        $processor = $processorFactory->createByTaskType($task['parser']);
        if ($processor) {
            $vulnerabilities = $processor->parseVulnerabilities($path);

            foreach ($vulnerabilities as $vulnerability) {
                $vulnRepository->insert($task['project_id'], $targetId, $item->userId, $vulnerability->summary, $vulnerability->description, 'medium');
            }
        }
    }
}
