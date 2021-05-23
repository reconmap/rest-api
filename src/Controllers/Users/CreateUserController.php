<?php declare(strict_types=1);

namespace Reconmap\Controllers\Users;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditLogAction;
use Reconmap\Models\User;
use Reconmap\Repositories\UserRepository;
use Reconmap\Services\AuditLogService;
use Reconmap\Services\EmailService;
use Reconmap\Services\PasswordGenerator;

class CreateUserController extends Controller
{
    public function __construct(
        private UserRepository $userRepository,
        private PasswordGenerator $passwordGenerator,
        private EmailService $emailService,
        private AuditLogService $auditLogService
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

        $user->password = password_hash($user->unencryptedPassword, PASSWORD_DEFAULT);

        $user->id = $this->userRepository->create($user);

        $loggedInUserId = $request->getAttribute('userId');

        $this->auditAction($loggedInUserId, $user->id);

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

        return (array)$user;
    }

    private function auditAction(int $loggedInUserId, int $userId): void
    {
        $this->auditLogService->insert($loggedInUserId, AuditLogAction::USER_CREATED, ['type' => 'user', 'id' => $userId]);
    }
}
