<?php declare(strict_types=1);

namespace Reconmap\Controllers\Users;

use League\Route\Http\Exception\UnauthorizedException;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditLogAction;
use Reconmap\Repositories\UserRepository;
use Reconmap\Services\AuditLogService;
use Reconmap\Services\EmailService;

class UpdateUserPasswordController extends Controller
{
    public function __construct(private UserRepository $userRepository,
                                private EmailService $emailService,
                                private AuditLogService $auditLogService)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $userId = (int)$args['userId'];
        $loggedInUserId = $request->getAttribute('userId');

        if ($loggedInUserId != $userId) {
            $this->logger->warning("Attempt to change password of a different user. (URL: $userId, JWT: $loggedInUserId");
            throw new UnauthorizedException();
        }

        $requestBody = $this->getJsonBodyDecoded($request);

        $user = $this->userRepository->findById($userId, true);

        if (is_null($user) || !password_verify($requestBody->currentPassword, $user['password'])) {
            $this->logger->warning("Wrong password entered during password change. (User ID: $userId)");
            throw new UnauthorizedException();
        }

        $hashedPassword = password_hash($requestBody->newPassword, PASSWORD_DEFAULT);

        $success = $this->userRepository->updateById($userId, ['password' => $hashedPassword]);

        $user = $this->userRepository->findById($userId);

        $templateVars = [
            'user_full_name' => $user['full_name']
        ];
        $this->emailService->queueTemplatedEmail('users/passwordChanged', $templateVars, 'Your password has been changed', [$user['email']]);

        $this->auditAction($loggedInUserId);

        return ['success' => $success];
    }

    private function auditAction(int $loggedInUserId): void
    {
        $this->auditLogService->insert($loggedInUserId, AuditLogAction::USER_PASSWORD_CHANGED);
    }
}
