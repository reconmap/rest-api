<?php declare(strict_types=1);

namespace Reconmap\Controllers;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Repositories\Deletable;
use Reconmap\Services\ActivityPublisherService;
use Reconmap\Services\Security\AuthorisationService;

abstract class DeleteEntityController extends Controller
{
    public function __construct(
        private AuthorisationService     $authorisationService,
        private ActivityPublisherService $activityPublisherService,
        private Deletable                $repository,
        private string                   $entityName,
        private string                   $auditLogAction,
        private string                   $idParamName
    )
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $operation = $this->entityName . '.delete';

        $role = $request->getAttribute('role');
        if (!$this->authorisationService->isRoleAllowed($role, $operation)) {
            $this->logger->warning("Unauthorised action '" . $operation . "' called for role '$role'");
            return ['success' => false];
        }

        $entityId = (int)$args[$this->idParamName];

        $success = $this->repository->deleteById($entityId);

        $userId = $request->getAttribute('userId');

        $this->auditAction($userId, $entityId);

        return ['success' => $success];
    }

    private function auditAction(int $loggedInUserId, int $entityId): void
    {
        $this->activityPublisherService->publish($loggedInUserId, $this->auditLogAction, ['type' => $this->entityName, 'id' => $entityId]);
    }
}
