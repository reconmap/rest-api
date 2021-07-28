<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\DatabaseTestCase;
use Reconmap\Models\Target;

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

    public function testInsert()
    {
        $target = new Target();
        $target->projectId = 5;
        $target->name = '192.168.0.1';
        $target->kind = 'hostname';

        $targetId = $this->subject->insert($target);
        $this->assertIsInt($targetId);
    }

    public function testFindById()
    {
        $target = $this->subject->findById(1);
        $this->assertEquals('url', $target['kind']);
    }

    public function testFindByIdNotFound()
    {
        $target = $this->subject->findById(-5);
        $this->assertNull($target);
    }
}
