<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Users;

use Firebase\JWT\JWT;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\AuthMiddleware;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditLogAction;
use Reconmap\Repositories\AuditLogRepository;
use Reconmap\Repositories\UserRepository;
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

		$response = new \GuzzleHttp\Psr7\Response;

		if (is_null($user) || !password_verify($password, $user['password'])) {
			return $response->withStatus(403);
		}

		unset($user['password']); // DOT NOT leak password in the response.

		$this->auditAction($user);
		$jwt = $this->getJWTPayload($user);
		
		$user['access_token'] = JWT::encode($jwt, AuthMiddleware::JWT_KEY, 'HS256');

		$response->getBody()->write(json_encode($user));
		return $response->withHeader('Content-type', 'application/json');
	}
	
	private function getJWTPayload(array $user) : array {
		
		$now = time();
		
		return [
			'iss' => 'reconmap.org',
			'aud' => 'reconmap.com',
			'iat' => $now,
			'nbf' => $now,
			'exp' => $now + (60 * 60), // 1 hour
			'data' => [
				'id' => $user['id'],
				'role' => $user['role']
			]
		];
		
	}

	private function auditAction(array $user): void
	{
		$clientIp = (new NetworkService)->getClientIp();
		$auditRepository = new AuditLogRepository($this->db);
		$auditRepository->insert($user['id'], $clientIp, AuditLogAction::USER_LOGGED_IN);
	}
}
