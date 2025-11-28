<?php declare(strict_types=1);

namespace Reconmap\Repositories\Exporters;

use PHPUnit\Framework\TestCase;
use Reconmap\Repositories\TargetRepository;

class TargetsExporterTest extends TestCase
{
    public function testHappyPath()
    {
        $mockRepository = $this->createMock(TargetRepository::class);
        $mockRepository->expects($this->once())
            ->method('findAll')
            ->with()
            ->willReturn([]);
        $exporter = new TargetsExporter($mockRepository);
        $this->assertEquals([], $exporter->export());
    }
}
