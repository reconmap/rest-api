<?php

declare(strict_types=1);

namespace Reconmap\Database;

use Reconmap\Models\Task;
use Reconmap\Repositories\TaskRepository;

class TaskTestDataGenerator
{
    public function __construct(private readonly TaskRepository $taskRepository) {}

    public function run(): void
    {
        $tasks = [
            [
                'created_by_uid' => 1,
                'project_id' => 1,
                'summary' => 'Run port scanner',
                'description' => 'Use nmap to detect all open ports',
            ],
        ];
        foreach ($tasks as $taskData) {
            $task = new Task();
            $task->created_by_uid = $taskData['created_by_uid'];
            $task->project_id = $taskData['project_id'];
            $task->summary = $taskData['summary'];
            $task->description = $taskData['description'];
            $this->taskRepository->insert($task);
        }

        $task = new Task();
        $task->created_by_uid = 1;
        $task->project_id = 5;
        $task->summary = 'Run port scanner';
        $task->description = 'Use nmap to detect all open ports';
        $this->taskRepository->insert($task);

        $task->created_by_uid = 1;
        $task->project_id = 5;
        $task->summary = 'Run SQL injection scanner';
        $task->description = 'Use sqlmap to test the application for SQL injection vulnerabilities';
        $this->taskRepository->insert($task);

        $task->created_by_uid = 1;
        $task->project_id = 5;
        $task->summary = 'Check domain expiration date';
        $task->description = 'Use whois or other tools to check when the domain expiration is.';
        $this->taskRepository->insert($task);

        $task->created_by_uid = 1;
        $task->project_id = 2;
        $task->summary = 'Run port scanner';
        $task->description = 'Use nmap to detect all open ports';
        $this->taskRepository->insert($task);

        $task->created_by_uid = 1;
        $task->project_id = 2;
        $task->summary = 'Run SQL injection scanner';
        $task->description = 'Use sqlmap to test the application for SQL injection vulnerabilities';
        $this->taskRepository->insert($task);

        $task->created_by_uid = 1;
        $task->project_id = 2;
        $task->summary = 'Check domain expiration date';
        $task->description = 'Use whois or other tools to check when the domain expiration is.';
        $this->taskRepository->insert($task);
    }
}
