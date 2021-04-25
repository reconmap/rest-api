<?php declare(strict_types=1);

namespace Reconmap\Controllers\Vulnerabilities;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\ControllerTestCase;
use Reconmap\Repositories\VulnerabilityRepository;
use Reconmap\Services\AuditLogService;

class BulkUpdateVulnerabilitiesControllerTest extends ControllerTestCase
{
    public function testSuccess(): void
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

        $mockVulnerabilityRepository = $this->createPartialMock(VulnerabilityRepository::class, ['deleteByIds']);
        $mockVulnerabilityRepository->expects($this->once())
            ->method('deleteByIds')
            ->with([1, 2, 3])
            ->willReturn(3);

        $mockAuditLogService = $this->createMock(AuditLogService::class);

        $controller = $this->injectController(new BulkUpdateVulnerabilitiesController($mockVulnerabilityRepository, $mockAuditLogService));
        $response = $controller($request);
        $this->assertEquals(3, $response['numSuccesses']);
    }
}
