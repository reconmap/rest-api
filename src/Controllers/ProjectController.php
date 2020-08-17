<?php

declare(strict_types=1);

namespace Reconmap\Controllers;

use Firebase\JWT\JWT;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ProjectController extends Controller
{

	public function handleRequest(ServerRequestInterface $request, array $args): ResponseInterface
	{
		$id = $args['id'];

		$stmt = $this->db->prepare('SELECT * FROM project WHERE id = ?');
		$stmt->bind_param('i', $id);
		$stmt->execute();
		$rs = $stmt->get_result();
		$project = $rs->fetch_assoc();
		$stmt->close();

		$response = new \GuzzleHttp\Psr7\Response;
		$response->getBody()->write(json_encode($project));
		return $response->withHeader('Access-Control-Allow-Origin', '*');
	}
}
