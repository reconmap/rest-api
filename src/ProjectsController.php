<?php
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ProjectsController extends Controller {

	public function handleRequest(ServerRequestInterface $request) : ResponseInterface {
		// @todo pull credentials from env variables
		$db = new \mysqli('db', 'reconmapper', 'reconmapped', 'reconmap');
		$rs = $db->query('SELECT * FROM project');
		$projects = $rs->fetch_all(MYSQLI_ASSOC);

		$response = new GuzzleHttp\Psr7\Response;
		$response->getBody()->write(json_encode($projects));
		return $response->withAddedHeader('content-type', 'application/json');
	}
}

