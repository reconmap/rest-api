<?php

declare(strict_types=1);

namespace Reconmap\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DeleteProjectController extends Controller
{

	public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
	{
		$id = $args['id'];

		$stmt = $this->db->prepare('DELETE FROM project WHERE id = ?');
		$stmt->bind_param('i', $id);
		$result = $stmt->execute();
		$success = $result && 1 === $stmt->affected_rows;
		$stmt->close();

		$response = new \GuzzleHttp\Psr7\Response;
		$response->getBody()->write(json_encode($success));
		return $response->withHeader('Access-Control-Allow-Origin', '*');
	}
}
