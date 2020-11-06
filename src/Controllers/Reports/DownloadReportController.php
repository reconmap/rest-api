<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Reports;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\ReportRepository;

class DownloadReportController extends Controller
{

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $id = (int)$args['id'];

        $repository = new ReportRepository($this->db);
        $report = $repository->findById($id);

        $response = new Response;

        $fileName = sprintf(RECONMAP_APP_DIR . "/data/reports/report-%d.%s", $id, $report['format']);
        $attachmentFileName = 'reconmap-report-' . date('Ymd-His') . '.' . $report['format'];

        $contentTypes = [
            'pdf' => 'application/pdf',
            'html' => 'text/html',
        ];

        return $response
            ->withHeader('Access-Control-Expose-Headers', 'Content-Disposition')
            ->withHeader('Content-Disposition', 'attachment; filename="' . $attachmentFileName . '";')
            ->withHeader('Content-type', $contentTypes[$report['format']])
            ->withBody(new Stream(fopen($fileName, 'r')));
    }
}
