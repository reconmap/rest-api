<?php

declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\DatabaseTestCase;
use Reconmap\Models\Document;

class DocumentRepositoryTest extends DatabaseTestCase
{
    private DocumentRepository $subject;

    public function setUp(): void
    {
        $this->subject = new DocumentRepository($this->getDatabaseConnection());
    }

    public function testInsert()
    {
        $document = new Document();
        $document->created_by_uid = 1;
        $document->parent_type = 'library';
        $document->visibility = 'public';
        $document->content = 'Hacker\'s stuff';

        $this->assertTrue($this->subject->insert($document) >= 1);
    }

    public function testFindById()
    {
        $document = $this->subject->findById(1);
        $this->assertEquals('Thing', $document['title']);
    }

    public function testFindByIdNotFound()
    {
        $document = $this->subject->findById(-5);
        $this->assertNull($document);
    }
}
