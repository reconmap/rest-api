<?php

declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\DatabaseTestCase;
use Reconmap\Models\Note;

class NoteRepositoryTest extends DatabaseTestCase
{
    public function testInsert()
    {
        $note = new Note();
        $note->created_by_uid = 1;
        $note->parent_type = 'project';
        $note->parent_id = 1;
        $note->visibility = 'public';
        $note->content = 'found this and that';

        $repository = new NoteRepository($this->getDatabaseConnection());
        $this->assertTrue($repository->insert($note) >= 1);
    }
}
