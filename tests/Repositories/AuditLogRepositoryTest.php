<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\DatabaseTestCase;

class AuditLogRepositoryTest extends DatabaseTestCase
{
    private AuditLogRepository $subject;

    public function setUp(): void
    {
        $this->subject = new AuditLogRepository($this->getDatabaseConnection());
    }

    public function testFindByInvalidUserId()
    {
        $logs = $this->subject->findByUserId(0);
        $this->assertEmpty($logs);
    }

    public function testFindByUserId()
    {
        $logs = $this->subject->findByUserId(1);
        $this->assertCount(10, $logs);
    }
}
