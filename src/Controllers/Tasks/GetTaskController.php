<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\TaskRepository;

class GetTaskController extends Controller
{

	public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
	{
		$id = (int)$args['id'];

		$repository = new TaskRepository($this->db);
		$task = $repository->findById($id);

		$response = new \GuzzleHttp\Psr7\Response;
		$response->getBody()->write(json_encode($task));
		return $response->withHeader('Access-Control-Allow-Origin', '*');
	}
}
