<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Reports;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\ReportRepository;

class GetReportsController extends Controller
{

	public function __invoke(ServerRequestInterface $request): ResponseInterface
	{
		$repository = new ReportRepository($this->db);
		$reports = $repository->findAll();

		$response = new \GuzzleHttp\Psr7\Response;
		$response->getBody()->write(json_encode($reports));
		return $response->withHeader('Access-Control-Allow-Origin', '*')
			->withAddedHeader('content-type', 'application/json');
	}
}
