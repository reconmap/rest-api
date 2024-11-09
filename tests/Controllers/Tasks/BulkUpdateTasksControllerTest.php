<?php declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\ConsecutiveParamsTrait;
use Reconmap\ControllerTestCase;
use Reconmap\Repositories\TaskRepository;
use Reconmap\Services\AuditLogService;

class BulkUpdateTasksControllerTest extends ControllerTestCase
{
    use ConsecutiveParamsTrait;

    public function testSuccessfulDeletes(): void
    {
        $userId = 1;

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('getAttribute')
            ->with('userId')
            ->willReturn($userId);
        $request->expects($this->once())
            ->method('getBody')
            ->willReturn(json_encode([1, 2, 3]));
        $request->expects($this->once())
            ->method('getHeaderLine')
            ->with('Bulk-Operation')
            ->willReturn('DELETE');

        $mockTaskRepository = $this->createPartialMock(TaskRepository::class, ['deleteByIds']);
        $mockTaskRepository->expects($this->once())
            ->method('deleteByIds')
            ->with([1, 2, 3])
            ->willReturn(3);

        $mockAuditLogService = $this->createMock(AuditLogService::class);

        $controller = $this->injectController(new BulkUpdateTasksController($mockTaskRepository, $mockAuditLogService));
        $response = $controller($request);
        $this->assertEquals(3, $response['numSuccesses']);
    }

    public function testSuccessfulUpdates(): void
    {
        $userId = 1;

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('getAttribute')
            ->with('userId')
            ->willReturn($userId);
        $request->expects($this->once())
            ->method('getBody')
            ->willReturn(json_encode(['taskIds' => [1, 2, 3], 'newStatus' => 'closed']));
        $request->expects($this->once())
            ->method('getHeaderLine')
            ->with('Bulk-Operation')
            ->willReturn('UPDATE');

        $mockTaskRepository = $this->createPartialMock(TaskRepository::class, ['updateById']);
        $mockTaskRepository->expects($this->exactly(3))
            ->method('updateById')
            ->with(...$this->consecutiveParams([1, ['status' => 'closed']], [2, ['status' => 'closed']], [3, ['status' => 'closed']]))
            ->willReturn(true);

        $mockAuditLogService = $this->createMock(AuditLogService::class);

        $controller = $this->injectController(new BulkUpdateTasksController($mockTaskRepository, $mockAuditLogService));
        $response = $controller($request);
        $this->assertEquals(3, $response['numSuccesses']);
    }
}
