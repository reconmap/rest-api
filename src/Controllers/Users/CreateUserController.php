<?php declare(strict_types=1);

namespace Reconmap\Controllers\Users;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditLogAction;
use Reconmap\Models\User;
use Reconmap\Repositories\UserRepository;
use Reconmap\Services\ApplicationConfig;
use Reconmap\Services\AuditLogService;
use Reconmap\Services\PasswordGenerator;
use Reconmap\Services\RedisServer;

class CreateUserController extends Controller
{
    public function __construct(
        private UserRepository $userRepository,
        private RedisServer $redisServer,
        private PasswordGenerator $passwordGenerator,
        private ApplicationConfig $applicationConfig,
        private AuditLogService $auditLogService
    )
    {
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        /** @var User $user */
        $user = $this->getJsonBodyDecodedAsClass($request, new User());

        $passwordGenerationMethodIsAuto = empty($user->unencryptedPassword);
        if ($passwordGenerationMethodIsAuto) {
            $user->unencryptedPassword = $this->passwordGenerator->generate(24);
        }

        $user->password = password_hash($user->unencryptedPassword, PASSWORD_DEFAULT);

        $user->id = $this->userRepository->create($user);

        $loggedInUserId = $request->getAttribute('userId');

        $this->auditAction($loggedInUserId, $user->id);

        $instanceUrl = $this->applicationConfig->getSettings('cors')['allowedOrigins'][0];

        if ($passwordGenerationMethodIsAuto || $user->sendEmailToUser) {
            $emailBody = $this->template->render('users/newAccount', [
                'instance_url' => $instanceUrl,
                'user' => (array)$user,
                'unencryptedPassword' => $user->unencryptedPassword
            ]);

            $result = $this->redisServer->lPush("email:queue",
                json_encode([
                    'subject' => 'Account created',
                    'to' => [$user->email => $user->full_name],
                    'body' => $emailBody
                ])
            );
            if (false === $result) {
                $this->logger->error('Item could not be pushed to the queue', ['queue' => 'email:queue']);
            }
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
