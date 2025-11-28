<?php declare(strict_types=1);

namespace Reconmap\Repositories\Exporters;

use PHPUnit\Framework\TestCase;
use Reconmap\Repositories\VulnerabilityRepository;

class VulnerabilitiesExporterTest extends TestCase
{
    public function testHappyPath()
    {
        $mockRepository = $this->createMock(VulnerabilityRepository::class);
        $mockRepository->expects($this->once())
            ->method('search')
            ->willReturn([]);
        $exporter = new VulnerabilitiesExporter($mockRepository);
        $this->assertEquals([], $exporter->export());
    }
}
