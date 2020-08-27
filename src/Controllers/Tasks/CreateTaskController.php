<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\TargetRepository;
use Reconmap\Repositories\TaskRepository;

class CreateTaskController extends Controller
{

	public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
	{
		$projectId = (int)$args['id'];
		$requestBody = json_decode((string)$request->getBody());

		$target = $requestBody;

		$repository = new TaskRepository($this->db);
		$result = $repository->insert($projectId, $target->parser, $target->name, $target->description);

		$response = new \GuzzleHttp\Psr7\Response;
		$response->getBody()->write(json_encode($result));
		return $response->withHeader('Access-Control-Allow-Origin', '*');
	}
}
