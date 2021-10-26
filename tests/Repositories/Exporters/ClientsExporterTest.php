<?php declare(strict_types=1);

namespace Reconmap\Repositories\Exporters;

use PHPUnit\Framework\TestCase;
use Reconmap\Repositories\ClientRepository;

class ClientsExporterTest extends TestCase
{
    public function testHappyPath()
    {
        $mockRepository = $this->createMock(ClientRepository::class);
        $mockRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([]);
        $exporter = new ClientsExporter($mockRepository);
        $this->assertEquals([], $exporter->export());
    }
}
