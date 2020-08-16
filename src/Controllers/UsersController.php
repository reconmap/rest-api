<?php declare(strict_types=1);

namespace Reconmap\Controllers;

use Firebase\JWT\JWT;
use League\Route\Http\Exception\ForbiddenException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UsersController extends Controller {

	public function handleRequest(ServerRequestInterface $request) : ResponseInterface {
		$json = $request->getParsedBody();
		$username = $json['username'];
		$password = $json['password'];

		$stmt = $this->db->prepare('SELECT * FROM user WHERE name = ? AND password = ?');
		$stmt->bind_param('ss', $username, $password);
		$stmt->execute();
		$rs = $stmt->get_result();
		$user = $rs->fetch_assoc();
		if(is_null($user)) {
			throw new ForbiddenException();
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
		$user['access_token'] = JWT::encode($jwt, self::JWT_KEY, 'HS256');

		$response = new \GuzzleHttp\Psr7\Response;
		$response->getBody()->write(json_encode($user));
		return $response->withHeader('Access-Control-Allow-Origin', '*')
				  ->withAddedHeader('content-type', 'application/json');
	}
}

