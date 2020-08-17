<?php

declare(strict_types=1);

namespace Reconmap\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Repositories\ProjectRepository;

class GetProjectsController extends Controller
{

	public function __invoke(ServerRequestInterface $request): ResponseInterface
	{
		$orderRepository = new ProjectRepository($this->db);

		$params = $request->getQueryParams();

		if(isset($params['isTemplate'])) {
			$projects = $orderRepository->findTemplateProjects((int)$params['isTemplate']);
		} else {
			$projects = $orderRepository->findAll();
		}

		$response = new \GuzzleHttp\Psr7\Response;
		$response->getBody()->write(json_encode($projects));
		return $response->withHeader('Access-Control-Allow-Origin', '*')
			->withAddedHeader('content-type', 'application/json');
	}
}
