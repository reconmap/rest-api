<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Reports;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\ReportRepository;

class DeleteReportController extends Controller
{

	public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
	{
		$id = (int)$args['id'];

		$repository = new ReportRepository($this->db);
		$report = $repository->findById($id);
		$success = $repository->deleteById($id);

		$filename = sprintf(RECONMAP_APP_DIR . "/data/report-%d.%s", $id, $report['format']);
		if (unlink($filename) === false) {
			$this->logger->warn("Unable to delete report file '$filename'");
		}

		$response = new \GuzzleHttp\Psr7\Response;
		$response->getBody()->write(json_encode($success));
		return $response->withHeader('Access-Control-Allow-Origin', '*');
	}
}
