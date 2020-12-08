<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Reports;

use Dompdf\Dompdf;
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

    private function saveHtmlToDisk(string $html, string $filePath)
    {
        file_put_contents($filePath . '.html', $html);
    }

    private function savePdfToDisk(string $html, string $filePath)
    {
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);

        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        file_put_contents($filePath . '.pdf', $dompdf->output());
    }

}
