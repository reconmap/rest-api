<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Users;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditLogAction;
use Reconmap\Repositories\UserRepository;
use Reconmap\Services\AuditLogService;
use Redis;

class CreateUserController extends Controller
{
    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $user = $this->getJsonBodyDecoded($request);

        $user->password = password_hash($user->password, PASSWORD_DEFAULT);

        $repository = new UserRepository($this->db);
        $userId = $repository->create($user);

        $loggedInUserId = $request->getAttribute('userId');

        $this->auditAction($loggedInUserId, $userId);

        if ((bool)($user->sendEmailToUser)) {
            $emailBody = $this->template->render('users/newAccount', [
                'user' => (array)$user
            ]);

            /** @var Redis $redis */
            $redis = $this->container->get(Redis::class);
            $result = $redis->lPush("email:queue",
                json_encode([
                    'subject' => 'Account created',
                    'to' => ['email' => $user->email, 'name' => $user->name],
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
        $auditLogService = new AuditLogService($this->db);
        $auditLogService->insert($loggedInUserId, AuditLogAction::USER_CREATED, ['type' => 'user', 'id' => $userId]);
    }
}
