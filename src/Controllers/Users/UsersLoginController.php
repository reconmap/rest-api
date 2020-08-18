<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Users;

use Exception;
use Firebase\JWT\JWT;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\AuthMiddleware;
use Reconmap\Controllers\Controller;
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
		$user = $repository->findByUsernamePassword($username, $password);

		$response = new \GuzzleHttp\Psr7\Response;

		if (is_null($user)) {
			return $response
				->withStatus(403)
				->withHeader('Access-Control-Allow-Methods', 'GET,POST,PUT')
				->withHeader('Access-Control-Allow-Origin', '*');
		}

		$action = 'Logged in';
		$clientIp = (new NetworkService)->getClientIp();
		$stmt = $this->db->prepare('INSERT INTO audit_log (user_id, client_ip, action) VALUES (?, INET_ATON(?), ?)');
		$stmt->bind_param('iss', $user['id'], $clientIp, $action);
		if (false === $stmt->execute()) {
			throw new Exception($stmt->error);
		}

		$now = time();
		$jwt = [
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
		$user['access_token'] = JWT::encode($jwt, AuthMiddleware::JWT_KEY, 'HS256');

		$response->getBody()->write(json_encode($user));
		return $response
			->withHeader('Access-Control-Allow-Methods', 'GET,POST,PUT')
			->withHeader('Access-Control-Allow-Origin', '*')
			->withAddedHeader('content-type', 'application/json');
	}
}
