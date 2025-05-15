<?php

declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\DatabaseTestCase;

class ReportRepositoryTest extends DatabaseTestCase
{
    private ReportRepository $subject;

    public function setUp(): void
    {
        $this->subject = new ReportRepository($this->getDatabaseConnection());
    }

    public function testAllVersionsAreReturnedForValidProject()
    {
        $reports = $this->subject->findByProjectId(2);
        $this->assertCount(0, $reports);
    }

    public function testNoVersionsAreReturnedForInvalidProject()
    {
        $reports = $this->subject->findByProjectId(-1);
        $this->assertEmpty($reports);
    }
}
