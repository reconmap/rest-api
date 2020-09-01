<?php

declare(strict_types=1);

namespace Reconmap\Repositories;

use PHPUnit\Framework\TestCase;

class TargetRepositoryTest extends TestCase
{
    private TargetRepository $subject;

    public function setUp(): void
    {
        $db = new \mysqli('db', 'reconmapper', 'reconmapped', 'reconmap');
        $this->subject = new TargetRepository($db);
    }

    public function testFindAllReturnsAllRecords()
    {
        $targets = $this->subject->findAll();
        $this->assertCount(2, $targets);
    }
}
