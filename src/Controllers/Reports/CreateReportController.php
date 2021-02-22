<?php declare(strict_types=1);

namespace Reconmap\Controllers\Reports;

use Knp\Snappy\Pdf;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\Attachment;
use Reconmap\Models\Report;
use Reconmap\Models\ReportConfiguration;
use Reconmap\Repositories\AttachmentRepository;
use Reconmap\Repositories\ProjectRepository;
use Reconmap\Repositories\ReportConfigurationRepository;
use Reconmap\Repositories\ReportRepository;
use Reconmap\Services\AttachmentFilePath;
use Reconmap\Services\Config;
use Reconmap\Services\ConfigConsumer;
use Reconmap\Services\ReportGenerator;

class CreateReportController extends Controller implements ConfigConsumer
{
    private ?Config $config = null;

    public function __construct(private AttachmentFilePath $attachmentFilePathService)
    {
    }

    public function setConfig(Config $config): void
    {
        $this->config = $config;
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $params = $this->getJsonBodyDecodedAsArray($request);
        $projectId = (int)$params['projectId'];
        $userId = $request->getAttribute('userId');

        $versionName = $params['name'];

        $report = new Report();
        $report->generatedByUid = $userId;
        $report->projectId = $projectId;
        $report->versionName = $versionName;
        $report->versionDescription = $params['description'];

        $projectRepository = new ProjectRepository($this->db);
        $project = $projectRepository->findById($projectId);

        $reportRepository = new ReportRepository($this->db);
        $reportId = $reportRepository->insert($report);

        $reportGenerator = new ReportGenerator($this->config, $this->db, $this->template);
        $reportSections = $reportGenerator->generate($projectId, $reportId);

        $reportConfiguration = new ReportConfigurationRepository($this->db);
        $config = $reportConfiguration->findByProjectId($projectId);

        $basePath = $this->attachmentFilePathService->generateBasePath();

        $attachment = new Attachment();
        $attachment->parent_type = 'report';
        $attachment->parent_id = $reportId;
        $attachment->submitter_uid = $userId;

        $attachmentIds = [];

        $repository = new AttachmentRepository($this->db);
        $attachmentIds[] = $repository->insert($this->generateHtmlReportAndAttachment($project, $attachment, $reportSections, $basePath, $versionName));
        $attachmentIds[] = $repository->insert($this->generatePdfReportAndAttachment($project, $attachment, $reportSections, $basePath, $config, $versionName));

        return $attachmentIds;
    }

    private function generateHtmlReportAndAttachment(array $project, Attachment $attachment, array $reportSections, string $basePath, string $versionName): Attachment
    {
        $projectName = str_replace(' ', '_', strtolower($project['name']));
        $clientFileName = "reconmap-{$projectName}-v{$versionName}.html";

        $fileName = uniqid(gethostname());
        $filePath = $basePath . $fileName;
        file_put_contents($filePath, $reportSections['body']);

        $attachment->file_name = $fileName;
        $attachment->file_mimetype = 'text/html';
        $attachment->file_hash = hash_file('md5', $filePath);
        $attachment->file_size = filesize($filePath);
        $attachment->client_file_name = $clientFileName;

        return $attachment;
    }

    private function generatePdfReportAndAttachment(array $project, Attachment $attachment, array $reportSections, string $basePath, ReportConfiguration $config, string $versionName): Attachment
    {
        $pdf = new Pdf('/usr/local/bin/wkhtmltopdf');
        // @todo Create own logger $pdf->setLogger($this->logger);

        $pdf->setOption('no-background', false);

        // Table of contents and outline
        if ($config->include_toc) {
            $pdf->setOption('toc', true);
            $pdf->setOption('xsl-style-sheet', $this->config->getAppDir() . '/resources/templates/reports/toc.xsl');
        }
        $pdf->setOption('outline-depth', 2);

        // Margins and paddings
        $pdf->setOption('header-spacing', 15);
        $pdf->setOption('footer-spacing', 15);
        $pdf->setOption('margin-left', 0);
        $pdf->setOption('margin-right', 0);
        // $pdf->setOption('margin-top', 0); # This breaks the whole layout
        $pdf->setOption('margin-bottom', 5);

        // Content
        if ($config->include_cover !== 'none') {
            $pdf->setOption('cover', $reportSections['cover']);
        }
        if ($config->include_header !== 'none') {
            $pdf->setOption('header-html', $reportSections['header']);
        }
        if ($config->include_footer !== 'none') {
            $pdf->setOption('footer-html', $reportSections['footer']);
        }

        $fileName = uniqid(gethostname());
        $filePath = $basePath . $fileName;

        $pdf->generateFromHtml($reportSections['body'], $filePath);

        $projectName = str_replace(' ', '_', strtolower($project['name']));
        $clientFileName = "reconmap-{$projectName}-v{$versionName}.pdf";

        $attachment->file_name = $fileName;
        $attachment->file_mimetype = 'application/pdf';
        $attachment->file_hash = hash_file('md5', $filePath);
        $attachment->file_size = filesize($filePath);
        $attachment->client_file_name = $clientFileName;

        return $attachment;
    }
}
