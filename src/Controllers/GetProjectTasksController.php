<?php

declare(strict_types=1);

namespace Reconmap\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetProjectTasksController extends Controller
{

	public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
	{
		$id = $args['id'];

		$stmt = $this->db->prepare('SELECT * FROM task WHERE project_id = ?');
		$stmt->bind_param('i', $id);
		$stmt->execute();
		$rs = $stmt->get_result();
		$tasks = $rs->fetch_all(MYSQLI_ASSOC);
		$stmt->close();

		$response = new \GuzzleHttp\Psr7\Response;
		$response->getBody()->write(json_encode($tasks));
		return $response->withHeader('Access-Control-Allow-Origin', '*')
			->withAddedHeader('content-type', 'application/json');
	}
}
