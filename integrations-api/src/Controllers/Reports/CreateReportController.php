<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Reports;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\Report;
use Reconmap\Repositories\AttachmentRepository;
use Reconmap\Repositories\ProjectRepository;
use Reconmap\Repositories\ReportRepository;
use Reconmap\Services\Filesystem\AttachmentFilePath;
use Reconmap\Services\Reporting\Renderers\PlainTextReportRenderer;
use Reconmap\Services\Reporting\Renderers\WordReportRenderer;
use Reconmap\Services\Reporting\ReportDataCollector;

class CreateReportController extends Controller
{
    public function __construct(
        private readonly AttachmentFilePath   $attachmentFilePathService,
        private readonly ProjectRepository    $projectRepository,
        private readonly ReportRepository     $reportRepository,
        private readonly AttachmentRepository $attachmentRepository,
        private readonly ReportDataCollector  $reportDataCollector,
    ) {}

    public function __invoke(ServerRequestInterface $request): array
    {
        $params = $this->getJsonBodyDecodedAsArray($request);
        $projectId = intval($params['projectId']);
        $reportTemplateId = intval($params['reportTemplateId']);

        $userId = $request->getAttribute('userId');

        $versionName = $params['name'];

        $attachments = $this->attachmentRepository->findByParentId('report', $reportTemplateId);
        if (empty($attachments)) {
            throw new \Exception("Report template with template id $reportTemplateId not found");
        }
        $reportTemplateAttachment = $attachments[0];

        $report = new Report();
        $report->created_by_uid = $userId;
        $report->project_id = $projectId;
        $report->version_name = $versionName;
        $report->version_description = $params['description'];
        $report->id = $this->reportRepository->insert($report);

        $project = $this->projectRepository->findById($projectId);

        $vars = $this->reportDataCollector->collectForProject($projectId);

        $attachmentIds = [];

        try {
            $ext = pathinfo($reportTemplateAttachment['client_file_name'], PATHINFO_EXTENSION);
            switch ($ext) {
                case 'docx':
                    $wordReportRenderer = new WordReportRenderer($this->logger, $this->attachmentFilePathService, $this->attachmentRepository);
                    $attachmentIds[] = $wordReportRenderer->render($project, $report, $reportTemplateAttachment, $vars);
                    break;
                case 'txt':
                case 'md':
                case 'tex':
                case 'html':
                    $textReportRenderer = new PlainTextReportRenderer($this->logger, $this->attachmentFilePathService, $this->attachmentRepository);
                    $attachmentIds[] = $textReportRenderer->render($project, $report, $reportTemplateAttachment, $vars);
                    break;
            }
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $this->logger->error("General error: $msg");
        }

        return $attachmentIds;
    }
}
