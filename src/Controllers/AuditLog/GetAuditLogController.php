<?php

declare(strict_types=1);

namespace Reconmap\Controllers\AuditLog;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\AuditLogRepository;

class GetAuditLogController extends Controller
{

	public function __invoke(ServerRequestInterface $request): ResponseInterface
	{
		$params = $request->getQueryParams();
		$page = (int)$params['page'];
		$this->logger->debug("page: $page");

		$repository = new AuditLogRepository($this->db);
		$auditLog = $repository->findAll($page);
		$count = $repository->countAll();

		$pageCount = ceil($count / 20);

		$response = new \GuzzleHttp\Psr7\Response;
		$response->getBody()->write(json_encode($auditLog));
		return $response
			->withHeader('Access-Control-Expose-Headers', 'X-Page-Count')
			->withHeader('X-Page-Count', $pageCount);
	}
}
