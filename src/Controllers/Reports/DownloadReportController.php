<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Reports;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use GuzzleHttp\Psr7\Stream;
use Reconmap\Repositories\ReportRepository;

class DownloadReportController extends Controller
{

	public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
	{
		$id = (int)$args['id'];

		$repository = new ReportRepository($this->db);
		$report = $repository->findById($id);

		$response = new \GuzzleHttp\Psr7\Response;

		$filename = sprintf(RECONMAP_APP_DIR . "/data/report-%d.%s", $id, $report['format']);

		if ($report['format'] === 'html') {
			return $response
			->withBody(new Stream(fopen($filename, 'r')))
			->withHeader('Access-Control-Expose-Headers', 'Content-Disposition')
			->withHeader('Access-Control-Allow-Origin', '*')
			->withHeader('Content-Disposition', 'attachment; filename="report.html";')
			->withHeader('Content-type', 'text/html');
		} else {
			return $response
				->withBody(new Stream(fopen($filename, 'r')))
				->withHeader('Access-Control-Expose-Headers', 'Content-Disposition')
				->withHeader('Access-Control-Allow-Origin', '*')
				->withHeader('Content-Disposition', 'attachment; filename="report.pdf";')
				->withHeader('Content-type', 'application/pdf');
		}
	}
}
