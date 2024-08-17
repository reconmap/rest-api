<?php declare(strict_types=1);

namespace Reconmap\Controllers;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Repositories\Deletable;
use Reconmap\Services\ActivityPublisherService;
use Reconmap\Services\Security\AuthorisationService;

abstract class DeleteEntityController extends Controller
{
    public function __construct(
        private readonly AuthorisationService     $authorisationService,
        private readonly ActivityPublisherService $activityPublisherService,
        private readonly Deletable                $repository,
        private readonly string                   $entityName,
        private readonly string                   $auditLogAction,
        private readonly string                   $idParamName
    )
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $operation = $this->entityName . '.delete';

        $role = $request->getAttribute('role');
        if (!$this->authorisationService->isRoleAllowed($role, $operation)) {
            $this->logger->warning("Unauthorised action '" . $operation . "' called for role '$role'");

            return (new Response())->withStatus(\Symfony\Component\HttpFoundation\Response::HTTP_FORBIDDEN);
        }

        $entityId = intval($args[$this->idParamName]);

        $success = $this->repository->deleteById($entityId);

        if ($success) {
            $userId = $request->getAttribute('userId');
            $this->auditAction($userId, $entityId);

            return $this->createNoContentResponse();
        }

        return (new Response())->withStatus(\Symfony\Component\HttpFoundation\Response::HTTP_BAD_REQUEST);
    }

    private function auditAction(int $loggedInUserId, int $entityId): void
    {
        $this->activityPublisherService->publish($loggedInUserId, $this->auditLogAction, ['type' => $this->entityName, 'id' => $entityId]);
    }
}
