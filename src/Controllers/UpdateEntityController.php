<?php declare(strict_types=1);

namespace Reconmap\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Repositories\Updateable;
use Reconmap\Services\ActivityPublisherService;
use Reconmap\Services\Security\AuthorisationService;

abstract class UpdateEntityController extends Controller
{
    public function __construct(
        private AuthorisationService     $authorisationService,
        private ActivityPublisherService $activityPublisherService,
        private Updateable               $repository,
        private string                   $entityName,
        private string                   $auditLogAction,
        private string                   $idParamName)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $operation = $this->entityName . '.update';

        $role = $request->getAttribute('role');
        if (!$this->authorisationService->isRoleAllowed($role, $operation)) {
            $this->logger->warning("Unauthorised action '" . $operation . "' called for role '$role'");

            return $this->createForbiddenResponse();
        }

        $entityId = intval($args[$this->idParamName]);

        $requestBody = $this->getJsonBodyDecodedAsArray($request);
        $newColumnValues = array_filter(
            $requestBody,
            fn(string $key) => in_array($key, array_keys($this->repository::UPDATABLE_COLUMNS_TYPES)),
            ARRAY_FILTER_USE_KEY
        );

        if (!empty($newColumnValues)) {
            $this->repository->updateById($entityId, $newColumnValues);

            $loggedInUserId = $request->getAttribute('userId');
            $this->auditAction($loggedInUserId, $entityId);
        }

        return $this->createNoContentResponse();
    }

    private function auditAction(int $loggedInUserId, int $entityId): void
    {
        $this->activityPublisherService->publish($loggedInUserId, $this->auditLogAction, ['type' => $this->entityName, 'id' => $entityId]);
    }
}
