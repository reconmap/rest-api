<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\ProjectRepository;

class GetProjectController extends Controller
{

	public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
	{
		$id = (int)$args['id'];

		$repository = new ProjectRepository($this->db);
		$project = $repository->findById($id);

		$response = new \GuzzleHttp\Psr7\Response;
		$response->getBody()->write(json_encode($project));
		return $response->withHeader('Access-Control-Allow-Origin', '*');
	}
}
