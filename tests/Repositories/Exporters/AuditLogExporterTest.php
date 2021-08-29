<?php declare(strict_types=1);

namespace Reconmap\Repositories\Exporters;

use PHPUnit\Framework\TestCase;
use Reconmap\Repositories\AuditLogRepository;

class AuditLogExporterTest extends TestCase
{
    public function testHappyPath()
    {
        $mockRepository = $this->createMock(AuditLogRepository::class);
        $mockRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([]);
        $exporter = new AuditLogExporter($mockRepository);
        $this->assertEquals([], $exporter->export('auditlog'));
    }
}
