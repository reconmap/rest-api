<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Reports;

use Dompdf\Dompdf;
use HeadlessChromium\BrowserFactory;
use HeadlessChromium\Page;
use Knp\Snappy\Pdf;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\Report;
use Reconmap\Repositories\ReportRepository;
use Reconmap\Services\Config;
use Reconmap\Services\ConfigConsumer;
use Reconmap\Services\ReportGenerator;

class CreateReportController extends Controller implements ConfigConsumer
{
    private ?Config $config = null;

    public function setConfig(Config $config): void
    {
        $this->config = $config;
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $params = $this->getJsonBodyDecodedAsArray($request);
        $projectId = (int)$params['projectId'];
        $userId = $request->getAttribute('userId');

        $report = new Report();
        $report->generatedByUid = $userId;
        $report->projectId = $projectId;
        $report->versionName = $params['name'];
        $report->versionDescription = $params['description'];

        $reportRepository = new ReportRepository($this->db);
        $reportId = $reportRepository->insert($report);

        $reportGenerator = new ReportGenerator($this->config, $this->db, $this->template);
        $html = $reportGenerator->generate($projectId, $reportId);

        $basePath = $this->config->getSetting('appDir') . '/data/reports/';
        $baseFileName = sprintf('report-%d-%d', $projectId, $reportId);
        $filePath = $basePath . $baseFileName;

        $this->saveHtmlToDisk($html, $filePath);
        $this->savePdfToDisk($html, $filePath);

        return [true];
    }

    private function saveHtmlToDisk(array $html, string $filePath)
    {
        file_put_contents($filePath . '.html', $html['body']);
    }

    private function savePdfToDisk(array $html, string $filePath)
    {
        $pdf = new Pdf('/usr/local/bin/wkhtmltopdf');
        $pdf->setLogger($this->logger);

        $pdf->setOption('no-background', false);

        // Table of contents and outline
        $pdf->setOption('toc', true);
        $pdf->setOption('xsl-style-sheet', RECONMAP_APP_DIR . '/resources/templates/reports/toc.xsl');
        $pdf->setOption('outline-depth', 2);

        // Margins and paddings
        $pdf->setOption('header-spacing', 15);
        $pdf->setOption('footer-spacing', 15);
        $pdf->setOption('margin-left', 0);
        $pdf->setOption('margin-right', 0);
        // $pdf->setOption('margin-top', 0); # This breaks the whole layout
        $pdf->setOption('margin-bottom', 5);

        // Content
        $pdf->setOption('cover', $html['cover']);
        $pdf->setOption('header-html', $html['header']);
        $pdf->setOption('footer-html', $html['footer']);

        $pdf->generateFromHtml($html['body'], $filePath . '.pdf');
    }
}
