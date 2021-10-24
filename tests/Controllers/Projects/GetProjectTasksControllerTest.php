<?php declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Models\Task;
use Reconmap\Repositories\TaskRepository;

class GetProjectTasksControllerTest extends TestCase
{
    public function testHappyPath()
    {
        $mockTaskRepository = $this->createMock(TaskRepository::class);
        $mockTaskRepository->expects($this->once())
            ->method('findByProjectId')
            ->with(1)
            ->willReturn([new Task(), new Task()]);
        $mockServerRequest = $this->createMock(ServerRequestInterface::class);

        $subject = new GetProjectTasksController($mockTaskRepository);
        $tasks = $subject($mockServerRequest, ['projectId' => 1]);
        $this->assertCount(2, $tasks);
    }
}
