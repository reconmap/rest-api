<?php declare(strict_types=1);

namespace Reconmap\Repositories\Exporters;

use PHPUnit\Framework\TestCase;
use Reconmap\Repositories\CommandRepository;

class CommandsExporterTest extends TestCase
{
    public function testHappyPath()
    {
        $mockRepository = $this->createMock(CommandRepository::class);
        $mockRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([]);
        $exporter = new CommandsExporter($mockRepository);
        $this->assertEquals([], $exporter->export('commands'));
    }
}
