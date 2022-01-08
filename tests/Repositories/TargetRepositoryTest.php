<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\DatabaseTestCase;
use Reconmap\Models\Target;
use Reconmap\Repositories\SearchCriterias\TargetSearchCriteria;

class TargetRepositoryTest extends DatabaseTestCase
{
    private TargetRepository $subject;

    public function setUp(): void
    {
        $db = $this->getDatabaseConnection();
        $this->subject = new TargetRepository($db);
    }

    public function testInsert()
    {
        $target = new Target();
        $target->project_id = 5;
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

    public function testSearch()
    {
        $serchCriteria = new TargetSearchCriteria();
        $serchCriteria->addProjectCriterion(1);
        $targets = $this->subject->search($serchCriteria);
        $this->assertCount(1, $targets);
    }

    public function testFindAll()
    {
        $targets = $this->subject->findAll();
        $this->assertCount(3, $targets);
    }

}
