<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Targets;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\TargetRepository;

class CreateTargetController extends Controller
{

	public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
	{
		$projectId = (int)$args['id'];
		$requestBody = json_decode((string)$request->getBody());

		$target = $requestBody;

		$repository = new TargetRepository($this->db);
		$result = $repository->insert($projectId, $target->name, $target->kind);

		$response = new \GuzzleHttp\Psr7\Response;
		$response->getBody()->write(json_encode($result));
		return $response->withHeader('Access-Control-Allow-Origin', '*');
	}
}
