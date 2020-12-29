<?php declare(strict_types=1);

namespace Reconmap\Controllers\Users;

use Firebase\JWT\JWT;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\AuthMiddleware;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditLogAction;
use Reconmap\Repositories\UserRepository;
use Reconmap\Services\AuditLogService;
use Reconmap\Services\JwtPayloadCreator;

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
            $this->audit(0, AuditLogAction::USER_LOGIN_FAILED, ['username' => $username]);
            return $response->withStatus(403);
        }

        unset($user['password']); // DO NOT leak password in the response.

        $this->audit($user['id'], AuditLogAction::USER_LOGGED_IN);

        $jwtPayload = (new JwtPayloadCreator())
            ->createFromUserArray($user);

        $user['access_token'] = JWT::encode($jwtPayload, AuthMiddleware::JWT_KEY, 'HS256');

        $response->getBody()->write(json_encode($user));
        return $response->withHeader('Content-type', 'application/json');
    }

    private function audit(int $userId, string $action, ?array $object = null): void
    {
        $auditLogService = new AuditLogService($this->db);
        $auditLogService->insert($userId, $action, $object);
    }
}
