<?php declare(strict_types=1);

namespace Reconmap\Repositories\Importers;

use PHPUnit\Framework\TestCase;
use Reconmap\Models\Document;
use Reconmap\Repositories\DocumentRepository;

class DocumentsImporterTest extends TestCase
{
    public function testHappyPath()
    {
        $userId = 5;

        $document = new Document();
        $document->user_id = 5;

        $documents = [$document];

        $mockDocumentRepository = $this->createMock(DocumentRepository::class);
        $mockDocumentRepository->expects($this->once())
            ->method('insert')
            ->with($document);

        $importer = new DocumentsImporter($mockDocumentRepository);
        $result = $importer->import($userId, $documents);
        $this->assertEquals(1, $result['count']);
    }
}
