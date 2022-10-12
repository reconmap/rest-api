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
        private readonly KeycloakService   $keycloakService,
        private readonly UserRepository    $userRepository,
        private readonly PasswordGenerator $passwordGenerator,
        private readonly EmailService      $emailService,
        private readonly AuditLogService   $auditLogService
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

        $passwordGenerationMethodIsAuto = empty($user->unencryptedPassword);
        if ($passwordGenerationMethodIsAuto) {
            $user->unencryptedPassword = $this->passwordGenerator->generate(24);
        }

        $accessToken = $this->keycloakService->getAccessToken();
        $user->subject_id = $this->keycloakService->createUser($user, $user->unencryptedPassword, $accessToken);

        $user->id = $this->userRepository->create($user);

        if ($passwordGenerationMethodIsAuto || $user->sendEmailToUser) {
            $this->emailService->queueTemplatedEmail(
                'users/newAccount',
                [
                    'user' => (array)$user,
                    'unencryptedPassword' => $user->unencryptedPassword
                ],
                'Account created',
                [$user->email => $user->full_name]
            );
        } else {
            $this->logger->debug('Email invitation not sent', ['email' => $user->email]);
        }

        $loggedInUserId = $request->getAttribute('userId');

        $this->auditAction($loggedInUserId, $user->id);


        return (array)$user;
    }

    private function auditAction(int $loggedInUserId, int $userId): void
    {
        $this->auditLogService->insert($loggedInUserId, UserAuditActions::USER_CREATED, ['type' => 'user', 'id' => $userId]);
    }
}
