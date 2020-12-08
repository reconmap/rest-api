<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Reports;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\ReportRepository;

class GetReportController extends Controller
{

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $reportId = (int)$args['id'];

        $repository = new ReportRepository($this->db);
        $report = $repository->findById($reportId);

        $contentType = $request->getHeaderLine('Content-Type');
        $format = 'text/html' === $contentType ? 'html' : 'pdf';

        $response = new Response;

        $fileName = sprintf(RECONMAP_APP_DIR . "/data/reports/report-%d-%d.%s", $report['project_id'], $reportId, $format);
        $this->logger->info($fileName);
        $attachmentFileName = 'reconmap-report-' . date('Ymd-His') . '.' . $format;

        return $response
            ->withHeader('Access-Control-Expose-Headers', 'Content-Disposition')
            ->withHeader('Content-Disposition', 'attachment; filename="' . $attachmentFileName . '";')
            ->withHeader('Content-type', $contentType)
            ->withBody(new Stream(fopen($fileName, 'r')));
    }
}
