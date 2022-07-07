<?php declare(strict_types=1);

namespace Reconmap\Controllers\Users;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditActions\UserAuditActions;
use Reconmap\Models\User;
use Reconmap\Repositories\UserRepository;
use Reconmap\Services\AuditLogService;
use Reconmap\Services\EmailService;
use Reconmap\Services\KeycloakService;
use Reconmap\Services\PasswordGenerator;

class CreateUserController extends Controller
{
    public function __construct(
        private readonly KeycloakService $keycloakService,
        private UserRepository           $userRepository,
        private PasswordGenerator        $passwordGenerator,
        private EmailService             $emailService,
        private AuditLogService          $auditLogService
    )
    {
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        /** @var User $user */
        $user = $this->getJsonBodyDecodedAsClass($request, new class() extends User {
            public ?string $unencryptedPassword;
            public ?bool $sendEmailToUser;
        });

        $user->id = $this->userRepository->create($user);

        $loggedInUserId = $request->getAttribute('userId');

        $this->auditAction($loggedInUserId, $user->id);

        $accessToken = $this->keycloakService->getAccessToken();
        $this->keycloakService->createUser($user, $accessToken);

        return (array)$user;
    }

    private function auditAction(int $loggedInUserId, int $userId): void
    {
        $this->auditLogService->insert($loggedInUserId, UserAuditActions::USER_CREATED, ['type' => 'user', 'id' => $userId]);
    }
}
