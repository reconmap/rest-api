<?php declare(strict_types=1);

namespace Reconmap\Repositories\Exporters;

use PHPUnit\Framework\TestCase;
use Reconmap\Repositories\TaskRepository;

class TasksExporterTest extends TestCase
{
    public function testHappyPath()
    {
        $mockRepository = $this->createMock(TaskRepository::class);
        $mockRepository->expects($this->once())
            ->method('findAll')
            ->with(false, null)
            ->willReturn([]);
        $exporter = new TasksExporter($mockRepository);
        $this->assertEquals([], $exporter->export());
    }
}
