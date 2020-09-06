<?php

declare(strict_types=1);

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

    public function publish(int $loggedInUserId, string $action, ?array $object = null): void
    {
        $this->auditLogService->insert($loggedInUserId, $action, $object);

        $integrations = $this->integrationsRepository->findByInterface(ActivityPublisher::class);
        foreach ($integrations as $integration) {
            $integration->publishActivity($action);
        }
    }
}
