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
        $logs = $this->subject->findByUserId(-1);
        $this->assertEmpty($logs);
    }

    public function testFindByUserId()
    {
        $this->subject->insert(1, 'firefox', '127.0.0.1', 'test');
        $logs = $this->subject->findByUserId(1);
        $this->assertCount(1, $logs);
    }
}
