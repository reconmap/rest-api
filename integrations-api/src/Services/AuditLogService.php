<?php declare(strict_types=1);

namespace Reconmap\Services;

use Reconmap\Repositories\AuditLogRepository;

readonly class AuditLogService
{
    public function __construct(
        private AuditLogRepository $repository,
        private NetworkService     $networkService
    )
    {
    }

    public function insert(?int $subjectId, \BackedEnum $action, string $object, ?array $context = null): int
    {
        $clientIp = $this->networkService->getClientIp();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        return $this->repository->insert($subjectId, $userAgent, $clientIp, $action->value, $object, $context ? json_encode($context) : null);
    }
}
