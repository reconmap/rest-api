<?php declare(strict_types=1);

namespace Reconmap\Controllers;

use Firebase\JWT\JWT;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ProjectController extends Controller {

	public function handleRequest(ServerRequestInterface $request, array $args) : ResponseInterface {
		$jwt = explode(' ', $request->getHeader('Authorization')[0])[1];
		// @todo replace with RSA keys
		$token = JWT::decode($jwt, self::JWT_KEY, ['HS256']);

		$id = $args['id'];
		
		$stmt = $this->db->prepare('SELECT * FROM project WHERE id = ?');
		$stmt->bind_param('i', $id);
		$stmt->execute();
		$rs = $stmt->get_result();
		$project = $rs->fetch_assoc();

		$response = new \GuzzleHttp\Psr7\Response;
		$response->getBody()->write(json_encode($project));
		return $response->withHeader('Access-Control-Allow-Origin', '*');
	}
}

