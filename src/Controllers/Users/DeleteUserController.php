<?php declare(strict_types=1);

namespace Reconmap\Controllers\Users;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditActions\UserAuditActions;
use Reconmap\Repositories\UserRepository;
use Reconmap\Services\ActivityPublisherService;
use Reconmap\Services\KeycloakService;

class DeleteUserController extends Controller
{
    public function __construct(private UserRepository           $repository,
                                private ActivityPublisherService $activityPublisherService)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $userId = (int)$args['userId'];
        $loggedInUserId = $request->getAttribute('userId');

        $user = $this->repository->findById($userId);

        $kc = new KeycloakService();
        $kc->deleteUser($user);

        $success = $this->repository->deleteById($userId);

        $this->auditAction($loggedInUserId, $userId);

        return ['success' => $success];
    }

    private function auditAction(int $loggedInUserId, int $userId): void
    {
        $this->activityPublisherService->publish($loggedInUserId, UserAuditActions::USER_DELETED, ['id' => $userId]);
    }
}
