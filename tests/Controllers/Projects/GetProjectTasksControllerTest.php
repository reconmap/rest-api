<?php declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Models\Task;
use Reconmap\Repositories\SearchCriterias\TaskSearchCriteria;
use Reconmap\Repositories\TaskRepository;

class GetProjectTasksControllerTest extends TestCase
{
    public function testHappyPath()
    {
        $mockTaskRepository = $this->createMock(TaskRepository::class);
        $mockTaskRepository->expects($this->once())
            ->method('search')
            ->willReturn([new Task(), new Task()]);
        $mockSearchCriteria = $this->createMock(TaskSearchCriteria::class);
        $mockSearchCriteria->expects($this->once())
            ->method('addProjectCriterion')
            ->with(1);
        $mockServerRequest = $this->createMock(ServerRequestInterface::class);

        $subject = new GetProjectTasksController($mockTaskRepository, $mockSearchCriteria);
        $tasks = $subject($mockServerRequest, ['projectId' => 1]);
        $this->assertCount(2, $tasks);
    }
}
