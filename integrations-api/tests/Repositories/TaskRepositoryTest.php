<?php

declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\DatabaseTestCase;
use Reconmap\Models\Task;

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
        $this->assertCount(17, $tasks);
        $task = $tasks[0];
        $this->assertArrayHasKey('project_id', $task);
        $this->assertArrayHasKey('project_name', $task);
    }

    public function testFindByKeywords()
    {
        $tasks = $this->subject->findByKeywords('scanner');
        $this->assertCount(6, $tasks);
        $this->assertEquals('Run port scanner', $tasks[0]['summary']);
    }

    public function testSearchByProject()
    {
        $tasks = $this->subject->findByProjectId(5);
        $this->assertCount(3, $tasks);
    }

    public function testFindById()
    {
        $task = $this->subject->findById(11);
        $this->assertEquals(1, $task['project_is_template']);
        $this->assertEquals('Run port scanner', $task['summary']);
    }

    public function testFindByIdNotFound()
    {
        $task = $this->subject->findById(0);
        $this->assertNull($task);
    }

    public function testDeleteById()
    {
        $this->assertFalse($this->subject->deleteById(50));
    }

    public function testInsert()
    {
        $task = new Task();
        $task->created_by_uid = 1;
        $task->project_id = 1;
        $task->summary = 'Do all the things';

        $this->assertIsInt($this->subject->insert($task));
    }
}
