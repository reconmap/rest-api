<?php

declare(strict_types=1);

namespace Reconmap\Controllers\AuditLog;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\AuditLogRepository;

class GetAuditLogStatsController extends Controller
{

	public function __invoke(ServerRequestInterface $request): ResponseInterface
	{
		$repository = new AuditLogRepository($this->db);
		$auditLog = $repository->findCountByDayStats();

		$response = new \GuzzleHttp\Psr7\Response;
		$response->getBody()->write(json_encode($auditLog));
		return $response->withHeader('Access-Control-Allow-Origin', '*')
			->withAddedHeader('content-type', 'application/json');
	}
}
