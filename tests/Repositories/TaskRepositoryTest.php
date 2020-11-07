<?php

declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\DatabaseTestCase;

class TaskRepositoryTest extends DatabaseTestCase
{
    private TaskRepository $subject;

    public function setUp(): void
    {
        $this->subject = new TaskRepository($this->getDatabaseConnection());
    }

    public function testFindAllReturnsProjectInformation()
    {
        $tasks = $this->subject->findAll();
        $this->assertCount(3, $tasks);
        $task = $tasks[0];
        $this->assertArrayHasKey('project_id', $task);
        $this->assertArrayHasKey('project_name', $task);
    }
}
