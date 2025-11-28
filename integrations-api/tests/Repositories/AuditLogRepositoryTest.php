<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\DatabaseTestCase;
use Reconmap\Models\AuditActions\SystemAuditActions;

class AuditLogRepositoryTest extends DatabaseTestCase
{
    private readonly AuditLogRepository $subject;

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
        $this->subject->insert(1, 'firefox', '127.0.0.1', SystemAuditActions::TEST->value, 'Audit Log');
        $logs = $this->subject->findByUserId(1);
        $this->assertCount(1, $logs);
    }
}
