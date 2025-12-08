<?php

declare(strict_types=1);

namespace Reconmap\Repositories\Importers;

use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Reconmap\DomainObjects\Document;
use Reconmap\Repositories\DocumentRepository;

class DocumentsImporterTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testHappyPath()
    {
        $userId = 5;

        $jsonDoc = new \stdClass();
        $jsonDoc->created_by_uid = 5;
        $jsonDoc->visibility = 'public';
        $jsonDoc->parent_id = null;
        $jsonDoc->parent_type = 'library';

        $docModel = new Document();
        $docModel->created_by_uid = 5;
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
