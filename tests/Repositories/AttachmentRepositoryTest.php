<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\DatabaseTestCase;
use Reconmap\Models\Attachment;

class AttachmentRepositoryTest extends DatabaseTestCase
{
    public function testInsert()
    {
        $attachment = new Attachment();
        $attachment->parent_type = 'project';
        $attachment->parent_id = 1;
        $attachment->submitter_uid = 1;
        $attachment->client_file_name = 'notes.txt';
        $attachment->file_name = 'uniquename.txt';
        $attachment->file_hash = '123';
        $attachment->file_size = 5;

        $repository = new AttachmentRepository($this->getDatabaseConnection());
        $this->assertTrue($repository->insert($attachment) >= 1);
    }
}
