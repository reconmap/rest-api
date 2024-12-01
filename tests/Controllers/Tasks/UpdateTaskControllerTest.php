<?php declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Models\AuditActions\TaskAuditActions;
use Reconmap\Repositories\TaskRepository;
use Reconmap\Services\ActivityPublisherService;

class UpdateTaskControllerTest extends TestCase
{
    public function testHappyPath()
    {
        $fakeTaskId = 49;

        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->once())
            ->method('getBody')
            ->willReturn(Utils::streamFor('{"summary": "new task name"}'));
        $mockRequest->expects($this->once())
            ->method('getAttribute')
            ->with('userId')
            ->willReturn(9);

        $mockTaskRepository = $this->createMock(TaskRepository::class);
        $mockTaskRepository->expects($this->once())
            ->method('updateById')
            ->with($fakeTaskId, ['summary' => 'new task name'])
            ->willReturn(true);

        $mockPublisherService = $this->createMock(ActivityPublisherService::class);
        $mockPublisherService->expects($this->once())
            ->method('publish')
            ->with(9, TaskAuditActions::TASK_MODIFIED, ['type' => 'task', 'id' => $fakeTaskId]);

        $args = ['taskId' => $fakeTaskId];

        $controller = new UpdateTaskController($mockTaskRepository, $mockPublisherService);
        $response = $controller($mockRequest, $args);
        $this->assertEquals(['success' => true], $response);
    }
}
