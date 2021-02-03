<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Reports;

use Dompdf\Dompdf;
use HeadlessChromium\BrowserFactory;
use HeadlessChromium\Page;
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
        $browser = null;

        try {
            $browserFactory = new BrowserFactory('/opt/google/chrome/chrome');
            $browser = $browserFactory->createBrowser(['noSandbox' => true]);

            $htmlPath = $filePath . '.html';
            file_put_contents($htmlPath, $html);

            $page = $browser->createPage();
            $navigation = $page->navigate('file://' . $htmlPath);
            $navigation->waitForNavigation(Page::LOAD, 30000);

            $options = [
                'displayHeaderFooter' => true,
                'printBackground' => true,
                'headerTemplate' => '<div style="font-size: 10px; border-bottom: 1px solid gray; padding: 2px; width: 100%; display: flex;"><span class="date" style="flex: 50%;"></span><span style="flex: 50%; align-self: flex-end; text-transform: uppercase; text-align: right;">Content is confidential, do not redistribute</span></div>',
                'footerTemplate' => '<div style="font-size: 10px; text-align: center; border-top: 1px solid gray; padding: 2px; width: 100%;">Page <span class="pageNumber"></span> of <span class="totalPages"></span></div>',
                'pageRanges' => '2-4'
            ];
            $pdf = $page->pdf($options);
            $pdf->saveToFile($filePath . '.pdf');

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        } finally {
            if ($browser)
                $browser->close();
        }
    }

}
