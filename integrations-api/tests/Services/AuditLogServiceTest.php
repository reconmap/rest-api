<?php declare(strict_types=1);

namespace Reconmap\Services;

use PHPUnit\Framework\TestCase;
use Reconmap\Models\AuditActions\SystemAuditActions;
use Reconmap\Repositories\AuditLogRepository;

class AuditLogServiceTest extends TestCase
{
    public function testHappyPath()
    {
        $mockAuditLogRepository = $this->createMock(AuditLogRepository::class);
        $mockAuditLogRepository->expects($this->once())
            ->method('insert')
            ->with(400, null, '1.2.3.4', 'Tested', 'System')
            ->willReturn(1);

        $mockNetworkService = $this->createMock(NetworkService::class);
        $mockNetworkService->expects($this->once())
            ->method('getClientIp')
            ->willReturn('1.2.3.4');

        $service = new AuditLogService($mockAuditLogRepository, $mockNetworkService);
        $this->assertEquals(1, $service->insert(400, SystemAuditActions::TEST, 'System'));
    }
}
