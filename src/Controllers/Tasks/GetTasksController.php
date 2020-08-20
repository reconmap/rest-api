<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\TaskRepository;

class GetTasksController extends Controller
{

	public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
	{
		$repository = new TaskRepository($this->db);
		$tasks = $repository->findAll();

		$response = new \GuzzleHttp\Psr7\Response;
		$response->getBody()->write(json_encode($tasks));
		return $response->withHeader('Access-Control-Allow-Origin', '*');
	}
}
