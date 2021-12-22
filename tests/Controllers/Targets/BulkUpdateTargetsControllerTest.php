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
            ->willReturn(json_encode(['projectId' => $fakeProjectId, 'lines' => "192.168.0.1,ip_address\n127.0.0.1"]));
        $request->expects($this->once())
            ->method('getHeaderLine')
            ->with('Bulk-Operation')
            ->willReturn('CREATE');

        $target = new Target();
        $target->project_id = $fakeProjectId;
        $target->name = '192.168.0.1';
        $target->kind = 'ip_address';

        $target2 = new Target();
        $target2->project_id = $fakeProjectId;
        $target2->name = '127.0.0.1';
        $target2->kind = 'hostname';

        $mockTargetRepository = $this->createPartialMock(TargetRepository::class, ['insert']);
        $mockTargetRepository->expects($this->exactly(2))
            ->method('insert')
            ->withConsecutive([$target], [$target2])
            ->willReturnOnConsecutiveCalls(3, 4);

        $mockAuditLogService = $this->createMock(AuditLogService::class);

        $controller = $this->injectController(new BulkUpdateTargetsController($mockTargetRepository, $mockAuditLogService));
        $response = $controller($request);

        $this->assertEquals(StatusCodeInterface::STATUS_CREATED, $response->getStatusCode());
        $this->assertEquals('[3,4]', (string)$response->getBody());
    }
}
