<?php declare(strict_types=1);

namespace Reconmap\Controllers\Auth;

use GuzzleHttp\Psr7\HttpFactory;
use Psr\Http\Message\ResponseInterface;
use Reconmap\Controllers\ControllerV2;
use Reconmap\Http\ApplicationRequest;
use Reconmap\Models\AuditActions\UserAuditActions;
use Reconmap\Repositories\UserRepository;
use Reconmap\Services\AuditLogService;
use Reconmap\Services\PasswordGenerator;
use Reconmap\Services\RedisServer;
use Reconmap\Services\Security\Permissions;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends ControllerV2
{
    public function __construct(
        private readonly UserRepository  $userRepository,
        private readonly AuditLogService $auditLogService,
        private readonly RedisServer     $redisServer,
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

        $response = new Response();
        $response->setContent(json_encode($user));
        $response->headers->set('Content-type', 'application/json');

        $this->redisServer->set('static-token', $staticToken);
        $cookie = new Cookie('reconmap-static', $staticToken, time() + (3600 * 24), path: '/', secure: false, httpOnly: false);
        $response->headers->setCookie($cookie);

        $psr17Factory = new HttpFactory();
        $psrHttpFactory = new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

        return $psrHttpFactory->createResponse($response);
    }

    private function generateStaticToken(): string
    {
        $passwordGenerator = new PasswordGenerator();
        return $passwordGenerator->generate(20);
    }

    private function audit(?int $userId): void
    {
        $this->auditLogService->insert($userId, UserAuditActions::USER_LOGGED_IN, null);
    }
}
