<?php declare(strict_types=1);

namespace Reconmap\Services;

use Reconmap\Integrations\ActivityPublisher;
use Reconmap\Repositories\IntegrationsRepository;

class ActivityPublisherService
{

    private AuditLogService $auditLogService;
    private IntegrationsRepository $integrationsRepository;

    public function __construct(AuditLogService $auditLogService, IntegrationsRepository $integrationsRepository)
    {
        $this->auditLogService = $auditLogService;
        $this->integrationsRepository = $integrationsRepository;
    }

    public function publish(int $loggedInUserId, \BackedEnum $action, string $object, ?array $context = null): void
    {
        $this->auditLogService->insert($loggedInUserId, $action, $object, $context);

        /** @var ActivityPublisher $integrations */
        $integrations = $this->integrationsRepository->findByInterface(ActivityPublisher::class);
        foreach ($integrations as $integration) {
            $integration->publishActivity($action);
        }
    }
}
