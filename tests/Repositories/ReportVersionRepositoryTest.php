<?php

declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\DatabaseTestCase;

class ReportVersionRepositoryTest extends DatabaseTestCase
{
    private ReportVersionRepository $subject;

    public function setUp(): void
    {
        $this->subject = new ReportVersionRepository($this->getDatabaseConnection());
    }

    public function testAllVersionsAreReturnedForValidProject()
    {
        $versions = $this->subject->findByProjectId(2);
        $this->assertCount(3, $versions);
    }

    public function testNoVersionsAreReturnedForInvalidProject()
    {
        $versions = $this->subject->findByProjectId(-1);
        $this->assertEmpty($versions);
    }
}
