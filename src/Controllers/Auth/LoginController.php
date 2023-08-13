<?php declare(strict_types=1);

namespace Reconmap\Controllers\Auth;

use GuzzleHttp\Psr7\Response;
use HansOtt\PSR7Cookies\InvalidArgumentException;
use HansOtt\PSR7Cookies\SetCookie;
use Psr\Http\Message\ResponseInterface;
use Reconmap\Controllers\ControllerV2;
use Reconmap\Http\ApplicationRequest;
use Reconmap\Models\AuditActions\UserAuditActions;
use Reconmap\Repositories\UserRepository;
use Reconmap\Services\AuditLogService;
use Reconmap\Services\PasswordGenerator;
use Reconmap\Services\RedisServer;
use Reconmap\Services\Security\Permissions;

class LoginController extends ControllerV2
{
    public function __construct(
        private readonly UserRepository  $userRepository,
        private readonly AuditLogService $auditLogService,
        private readonly RedisServer $redisServer,
    )
    {
    }

    protected function getPermissionRequired(): string
    {
        return 'users.login';
    }

    protected function process(ApplicationRequest $request): ResponseInterface
    {
        $requestUser = $request->getUser();

        $this->audit($requestUser->id);

        $user = $this->userRepository->findById($requestUser->id);

        $user['permissions'] = Permissions::ByRoles[$requestUser->role];

        $staticToken = $this->generateStaticToken();

        $response = new Response;

        try {
            $this->redisServer->set('static-token', $staticToken);
            $cookie = new SetCookie('reconmap-static', $staticToken, time() + (3600 * 24), path: '/', secure: false, httpOnly: false);
            $response = $cookie->addToResponse($response);
        } catch (InvalidArgumentException $e) {
            $this->logger->warning($e->getMessage());
        }

        $response->getBody()->write(json_encode($user));
        return $response->withHeader('Content-type', 'application/json');
    }

    private function generateStaticToken(): string {
        $passwordGenerator = new PasswordGenerator();
        return $passwordGenerator->generate(20);
    }

    private function audit(?int $userId): void
    {
        $this->auditLogService->insert($userId, UserAuditActions::USER_LOGGED_IN, null);
    }
}
