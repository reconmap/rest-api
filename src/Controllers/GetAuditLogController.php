<?php

declare(strict_types=1);

namespace Reconmap\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Repositories\AuditLogRepository;

class GetAuditLogController extends Controller
{

	public function __invoke(ServerRequestInterface $request): ResponseInterface
	{
		$repository = new AuditLogRepository($this->db);
		$auditLog = $repository->findAll();

		$response = new \GuzzleHttp\Psr7\Response;
		$response->getBody()->write(json_encode($auditLog));
		return $response->withHeader('Access-Control-Allow-Origin', '*')
			->withAddedHeader('content-type', 'application/json');
	}
}
