<?php declare(strict_types=1);

namespace Reconmap\Repositories\Exporters;

use PHPUnit\Framework\TestCase;
use Reconmap\Repositories\DocumentRepository;

class DocumentsExporterTest extends TestCase
{
    public function testHappyPath()
    {
        $mockRepository = $this->createMock(DocumentRepository::class);
        $mockRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([]);
        $exporter = new DocumentsExporter($mockRepository);
        $this->assertEquals([], $exporter->export('clients'));
    }
}
