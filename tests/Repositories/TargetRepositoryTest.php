<?php

declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\DatabaseTestCase;

class TargetRepositoryTest extends DatabaseTestCase
{
    private TargetRepository $subject;

    public function setUp(): void
    {
        $db = $this->getDatabaseConnection();
        $this->subject = new TargetRepository($db);
    }

    public function testFindAllReturnsAllRecords()
    {
        $targets = $this->subject->findAll();
        $this->assertCount(2, $targets);
    }
}
