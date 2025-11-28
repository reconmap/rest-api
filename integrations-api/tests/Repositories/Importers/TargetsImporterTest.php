<?php declare(strict_types=1);

namespace Reconmap\Repositories\Importers;

use PHPUnit\Framework\TestCase;
use Reconmap\Models\Target;
use Reconmap\Repositories\TargetRepository;

class TargetsImporterTest extends TestCase
{
    public function testHappyPath()
    {
        $userId = 5;

        $jsonDoc = new \stdClass();
        $jsonDoc->project_id = 5;
        $jsonDoc->parent_id = 1;
        $jsonDoc->name = 'localhost';
        $jsonDoc->kind = 'host';

        $target = new Target();
        $target->project_id = 5;
        $target->parent_id = 1;
        $target->name = 'localhost';
        $target->kind = 'host';

        $targets = [$jsonDoc];

        $mockDocumentRepository = $this->createMock(TargetRepository::class);
        $mockDocumentRepository->expects($this->once())
            ->method('insert')
            ->with($target);

        $importer = new TargetsImporter($mockDocumentRepository);
        $result = $importer->import($userId, $targets);
        $this->assertEquals(1, $result['count']);
    }
}
