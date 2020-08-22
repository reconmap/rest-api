<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Users;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\AuditLogRepository;

class GetUserActivityController extends Controller
{

	public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
	{
		$id = (int)$args['id'];

		$repository = new AuditLogRepository($this->db);
		$results = $repository->findByUserId($id);

		$response = new \GuzzleHttp\Psr7\Response;
		$response->getBody()->write(json_encode($results));
		return $response->withHeader('Access-Control-Allow-Origin', '*');
	}
}
