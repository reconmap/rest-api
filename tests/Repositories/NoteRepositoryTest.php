<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\DatabaseTestCase;

class NoteRepositoryTest extends DatabaseTestCase
{
    public function testInsert()
    {
        $note = new \stdClass();
        $note->parentType = 'project';
        $note->parentId = 1;
        $note->visibility = 'public';
        $note->content = 'found this and that';

        $repository = new NoteRepository($this->getDatabaseConnection());
        $this->assertTrue($repository->insert(1, $note) >= 1);
    }
}
