<?php declare(strict_types=1);

namespace Reconmap\Controllers\Targets;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\ControllerTestCase;
use Reconmap\Models\Target;
use Reconmap\Repositories\TargetRepository;
use Reconmap\Services\AuditLogService;

class BulkUpdateTargetsControllerTest extends ControllerTestCase
{
    public function testSuccess(): void
    {
        $fakeProjectId = 4;
        $userId = 1;

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('getAttribute')
            ->with('userId')
            ->willReturn($userId);
        $request->expects($this->once())
            ->method('getBody')
            ->willReturn(json_encode(['projectId' => $fakeProjectId, 'lines' => '192.168.0.1,hostname']));
        $request->expects($this->once())
            ->method('getHeaderLine')
            ->with('Bulk-Operation')
            ->willReturn('CREATE');

        $target = new Target();
        $target->projectId = $fakeProjectId;
        $target->name = '192.168.0.1';
        $target->kind = 'hostname';

        $mockTargetRepository = $this->createPartialMock(TargetRepository::class, ['insert']);
        $mockTargetRepository->expects($this->once())
            ->method('insert')
            ->with($target)
            ->willReturn(3);

        $mockAuditLogService = $this->createMock(AuditLogService::class);

        $controller = $this->injectController(new BulkUpdateTargetsController($mockTargetRepository, $mockAuditLogService));
        $response = $controller($request);

        $this->assertEquals(StatusCodeInterface::STATUS_CREATED, $response->getStatusCode());
        $this->assertEquals('[3]', (string)$response->getBody());
    }
}
