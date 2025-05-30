<?php declare(strict_types=1);

namespace Reconmap\Controllers\Users;

use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditActions\UserAuditActions;
use Reconmap\Repositories\UserRepository;
use Reconmap\Services\ActivityPublisherService;
use Reconmap\Services\KeycloakService;
use Symfony\Component\HttpFoundation\Response;

class CreateUserActionController extends Controller
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

        $action = $this->getJsonBodyDecodedAsArray($request)['name'];

        try {
            switch ($action) {
                case 'enable-mfa':
                    if ($this->keycloakService->enableMfa($user)) {
                        $this->userRepository->updateById($userId, ['mfa_enabled' => true]);
                    }
                    break;
                case 'reset-password':
                    $this->keycloakService->resetPassword($user);
                    break;
            }

        } catch (ClientException $e) {
            if ($e->getCode() === Response::HTTP_NOT_FOUND) {
                $this->logger->warning("User to delete not found on Keycloak", ['userId' => $userId]);
            } else {
                throw $e;
            }
        }

        $this->auditAction($loggedInUserId, $userId, $action);

        return $this->createOkResponse();
    }

    private function auditAction(int $loggedInUserId, int $userId, string $action): void
    {
        $this->activityPublisherService->publish($loggedInUserId, UserAuditActions::USER_REQUESTED_ACTION, ['id' => $userId, 'action' => $action]);
    }
}
