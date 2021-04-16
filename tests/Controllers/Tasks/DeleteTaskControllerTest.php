<?php declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Models\AuditLogAction;
use Reconmap\Repositories\TaskRepository;
use Reconmap\Services\ActivityPublisherService;

class DeleteTaskControllerTest extends TestCase
{
    public function testSuccessfulDelete()
    {
        $fakeTaskId = 86;

        $mockTaskRepository = $this->createMock(TaskRepository::class);
        $mockTaskRepository->expects($this->once())
            ->method('deleteById')
            ->with($fakeTaskId)
            ->willReturn(true);

        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->once())
            ->method('getAttribute')
            ->with('userId')
            ->willReturn(9);

        $mockPublisherService = $this->createMock(ActivityPublisherService::class);
        $mockPublisherService->expects($this->once())
            ->method('publish')
            ->with(9, AuditLogAction::TASK_DELETED, ['type' => 'task', 'id' => $fakeTaskId]);

        $args = ['taskId' => $fakeTaskId];

        $controller = new DeleteTaskController($mockTaskRepository, $mockPublisherService);
        $response = $controller($mockRequest, $args);

        $this->assertTrue($response['success']);
    }
}
