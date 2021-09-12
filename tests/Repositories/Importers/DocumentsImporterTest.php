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

        $jsonDoc = new \stdClass();
        $jsonDoc->user_id = 5;
        $jsonDoc->visibility = 'public';
        $jsonDoc->parent_id = null;
        $jsonDoc->parent_type = 'library';

        $docModel = new Document();
        $docModel->user_id = 5;
        $docModel->visibility = 'public';
        $docModel->parent_id = null;
        $docModel->parent_type = 'library';

        $documents = [$jsonDoc];

        $mockDocumentRepository = $this->createMock(DocumentRepository::class);
        $mockDocumentRepository->expects($this->once())
            ->method('insert')
            ->with($docModel);

        $importer = new DocumentsImporter($mockDocumentRepository);
        $result = $importer->import($userId, $documents);
        $this->assertEquals(1, $result['count']);
    }
}
