<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Users;

use Firebase\JWT\JWT;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\AuthMiddleware;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditLogAction;
use Reconmap\Repositories\AuditLogRepository;
use Reconmap\Repositories\UserRepository;
use Reconmap\Services\JwtPayloadCreator;
use Reconmap\Services\NetworkService;

class UsersLoginController extends Controller
{

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $json = $request->getParsedBody();
        $username = $json['username'];
        $password = $json['password'];

        $repository = new UserRepository($this->db);
        $user = $repository->findByUsername($username);

        $response = new Response;

        if (is_null($user) || !password_verify($password, $user['password'])) {
            $this->auditFailedLogin($username);
            return $response->withStatus(403);
        }

        unset($user['password']); // DO NOT leak password in the response.

        $this->auditAction($user);

        $jwtPayload = (new JwtPayloadCreator())
            ->createFromUserArray($user);

        $user['access_token'] = JWT::encode($jwtPayload, AuthMiddleware::JWT_KEY, 'HS256');

        $response->getBody()->write(json_encode($user));
        return $response->withHeader('Content-type', 'application/json');
    }

    private function auditAction(array $user): void
    {
        $clientIp = (new NetworkService)->getClientIp();
        $auditRepository = new AuditLogRepository($this->db);
        $auditRepository->insert($user['id'], $clientIp, AuditLogAction::USER_LOGGED_IN);
    }

    private function auditFailedLogin(?string $username): void
    {
        $clientIp = (new NetworkService)->getClientIp();
        $auditRepository = new AuditLogRepository($this->db);
        $auditRepository->insert(0, $clientIp, AuditLogAction::USER_LOGIN_FAILED, json_encode(['username' => $username]));
    }
}
