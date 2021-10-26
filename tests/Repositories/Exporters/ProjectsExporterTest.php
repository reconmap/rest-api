<?php declare(strict_types=1);

namespace Reconmap\Repositories\Exporters;

use PHPUnit\Framework\TestCase;
use Reconmap\Repositories\ProjectRepository;

class ProjectsExporterTest extends TestCase
{
    public function testHappyPath()
    {
        $mockRepository = $this->createMock(ProjectRepository::class);
        $mockRepository->expects($this->once())
            ->method('search')
            ->willReturn([]);
        $exporter = new ProjectsExporter($mockRepository);
        $this->assertEquals([], $exporter->export());
    }
}
