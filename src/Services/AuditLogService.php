<?php

declare(strict_types=1);

namespace Reconmap\Services;

use Reconmap\Repositories\AuditLogRepository;

class AuditLogService
{

    private AuditLogRepository $repository;

    public function __construct(\mysqli $db)
    {
        $this->repository = new AuditLogRepository($db);
    }

    public function insert(int $loggedInUserId, string $action, ?array $object = null): int
    {
        $clientIp = (new NetworkService)->getClientIp();
        return $this->repository->insert($loggedInUserId, $clientIp, $action, $object ? json_encode($object) : null);
    }
}
