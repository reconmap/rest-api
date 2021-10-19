<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\DatabaseTestCase;
use Reconmap\Models\Task;
use Reconmap\Repositories\SearchCriterias\TaskSearchCriteria;

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
        $this->assertCount(6, $tasks);
        $task = $tasks[0];
        $this->assertArrayHasKey('project_id', $task);
        $this->assertArrayHasKey('project_name', $task);
    }

    public function testFindByKeywords()
    {
        $tasks = $this->subject->findByKeywords('SCANNER');
        $this->assertCount(6, $tasks);
        $this->assertEquals('Run port scanner', $tasks[0]['summary']);
    }

    public function testSearchByProject()
    {
        $searchCriteria = new TaskSearchCriteria();
        $searchCriteria->addProjectCriterion(1);
        $tasks = $this->subject->search($searchCriteria);
        $this->assertCount(3, $tasks);
    }

    public function testFindById()
    {
        $task = $this->subject->findById(1);
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
        $task->creator_uid = 1;
        $task->project_id = 1;
        $task->summary = 'Do all the things';

        $this->assertIsInt($this->subject->insert($task));
    }
}
