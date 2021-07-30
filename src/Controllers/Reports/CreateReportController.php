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
use Reconmap\Services\ApplicationConfig;
use Reconmap\Services\AttachmentFilePath;
use Reconmap\Services\ReportGenerator;

class CreateReportController extends Controller
{
    public function __construct(
        private AttachmentFilePath            $attachmentFilePathService,
        private ProjectRepository             $projectRepository,
        private ReportRepository              $reportRepository,
        private ReportConfigurationRepository $reportConfigurationRepository,
        private AttachmentRepository          $attachmentRepository,
        private ReportGenerator               $reportGenerator,
        private ApplicationConfig             $applicationConfig
    )
    {
    }

    public function __invoke(ServerRequestInterface $request): array
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

        $project = $this->projectRepository->findById($projectId);

        $reportId = $this->reportRepository->insert($report);

        $reportSections = $this->reportGenerator->generate($projectId, $reportId);

        $reportConfig = $this->reportConfigurationRepository->findByProjectId($projectId);

        $basePath = $this->attachmentFilePathService->generateBasePath();

        $attachment = new Attachment();
        $attachment->parent_type = 'report';
        $attachment->parent_id = $reportId;
        $attachment->submitter_uid = $userId;

        $attachmentIds = [];

        $attachmentIds[] = $this->attachmentRepository->insert($this->generateHtmlReportAndAttachment($project, $attachment, $reportSections, $basePath, $versionName));
        $attachmentIds[] = $this->attachmentRepository->insert($this->generatePdfReportAndAttachment($project, $attachment, $reportSections, $basePath, $reportConfig, $versionName));

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

    private function generatePdfReportAndAttachment(array $project, Attachment $attachment, array $reportSections, string $basePath, ReportConfiguration $reportConfig, string $versionName): Attachment
    {
        $pdfGenerator = new Pdf('/usr/local/bin/wkhtmltopdf');
        // @todo Create own logger $pdf->setLogger($this->logger);

        $pdfGenerator->setOption('no-background', false);

        // Table of contents and outline
        if ($reportConfig->include_toc) {
            $pdfGenerator->setOption('toc', true);
            $pdfGenerator->setOption('xsl-style-sheet', $this->applicationConfig->getAppDir() . '/resources/templates/reports/toc.xsl');
        }
        $pdfGenerator->setOption('outline-depth', 2);

        // Margins and paddings
        $pdfGenerator->setOption('header-spacing', 15);
        $pdfGenerator->setOption('footer-spacing', 15);
        $pdfGenerator->setOption('margin-left', 0);
        $pdfGenerator->setOption('margin-right', 0);
        // $pdf->setOption('margin-top', 0); # This breaks the whole layout
        $pdfGenerator->setOption('margin-bottom', 5);

        // Content
        if ($reportConfig->include_cover !== 'none') {
            $pdfGenerator->setOption('cover', $reportSections['cover']);
        }
        if ($reportConfig->include_header !== 'none') {
            $pdfGenerator->setOption('header-html', $reportSections['header']);
        }
        if ($reportConfig->include_footer !== 'none') {
            $pdfGenerator->setOption('footer-html', $reportSections['footer']);
        }

        $fileName = uniqid(gethostname());
        $filePath = $basePath . $fileName;

        $pdfGenerator->generateFromHtml($reportSections['body'], $filePath);

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
