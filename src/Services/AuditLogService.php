<?php declare(strict_types=1);

namespace Reconmap\Services;

use Reconmap\Repositories\AuditLogRepository;

class AuditLogService
{
    public function __construct(
        private AuditLogRepository $repository,
        private NetworkService     $networkService
    )
    {
    }

    public function insert(int $loggedInUserId, string $action, ?array $object = null): int
    {
        $clientIp = $this->networkService->getClientIp();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        return $this->repository->insert($loggedInUserId, $userAgent, $clientIp, $action, $object ? json_encode($object) : null);
    }
}
