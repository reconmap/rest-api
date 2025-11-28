<?php declare(strict_types=1);

namespace Reconmap\Controllers\Users;

use GuzzleHttp\Exception\ClientException;
use OpenApi\Attributes as OpenApi;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Http\Docs\Default204NoContentResponse;
use Reconmap\Http\Docs\Default403UnauthorisedResponse;
use Reconmap\Http\Docs\InPathIdParameter;
use Reconmap\Models\AuditActions\AuditActions;
use Reconmap\Repositories\UserRepository;
use Reconmap\Services\ActivityPublisherService;
use Reconmap\Services\KeycloakService;
use Symfony\Component\HttpFoundation\Response;

#[OpenApi\Delete(path: "/users/{userId}", description: "Deletes user with the given id", security: ["bearerAuth"], tags: ["Users"], parameters: [new InPathIdParameter("userId")])]
#[Default204NoContentResponse]
#[Default403UnauthorisedResponse]
class DeleteUserController extends Controller
{
    public function __construct(private readonly UserRepository           $userRepository,
                                private readonly KeycloakService          $keycloakService,
                                private readonly ActivityPublisherService $activityPublisherService)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $userId = (int)$args['userId'];
        $loggedInUserId = $request->getAttribute('userId');

        $user = $this->userRepository->findById($userId);

        try {
            $this->keycloakService->deleteUser($user);
        } catch (ClientException $e) {
            if ($e->getCode() === Response::HTTP_NOT_FOUND) {
                $this->logger->warning("User to delete not found on Keycloak", ['userId' => $userId]);
            } else {
                throw $e;
            }
        }

        $this->userRepository->deleteById($userId);

        $this->auditAction($loggedInUserId, $userId);

        return $this->createDeletedResponse();
    }

    private function auditAction(int $loggedInUserId, int $userId): void
    {
        $this->activityPublisherService->publish($loggedInUserId, AuditActions::DELETED, 'User', ['id' => $userId]);
    }
}
