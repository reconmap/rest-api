<?php declare(strict_types=1);

namespace Reconmap\Controllers\Targets;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\ControllerTestCase;
use Reconmap\Repositories\TargetRepository;
use Reconmap\Services\AuditLogService;

class DeleteTargetControllerTest extends ControllerTestCase
{

    public function testSuccess(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects($this->once())
            ->method('getAttribute')
            ->willReturn(1);
        $args = ['targetId' => 0];

        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $mockRepository = $this->createPartialMock(TargetRepository::class, ['deleteById']);
        $mockRepository->expects($this->once())
            ->method('deleteById')
            ->with(0)
            ->willReturn(true);

        $mockAuditLogService = $this->createPartialMock(AuditLogService::class, ['insert']);

        $controller = $this->injectController(new DeleteTargetController($mockRepository, $mockAuditLogService));
        $response = $controller($request, $args);
        $this->assertEquals(204, $response->getStatusCode());;
    }
}
