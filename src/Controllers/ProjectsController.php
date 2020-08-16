<?php declare(strict_types=1);

namespace Reconmap\Controllers;

use Firebase\JWT\JWT;
use League\Route\Http\Exception\ForbiddenException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ProjectsController extends Controller {

	public function handleRequest(ServerRequestInterface $request) : ResponseInterface {
		$rs = $this->db->query('SELECT * FROM project');
		$projects = $rs->fetch_all(MYSQLI_ASSOC);

		$this->validateJwtToken($request);

		$response = new \GuzzleHttp\Psr7\Response;
		$response->getBody()->write(json_encode($projects));
		return $response->withHeader('Access-Control-Allow-Origin', '*')
				  ->withAddedHeader('content-type', 'application/json');
	}
}

