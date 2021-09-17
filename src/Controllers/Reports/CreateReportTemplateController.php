<?php declare(strict_types=1);

namespace Reconmap\Controllers\Reports;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Attachments\UploadAttachmentController;
use Reconmap\Models\Report;
use Reconmap\Repositories\AttachmentRepository;
use Reconmap\Repositories\ReportRepository;
use Reconmap\Services\Filesystem\AttachmentFilePath;

class CreateReportTemplateController extends UploadAttachmentController
{
    public function __construct(AttachmentRepository     $attachmentRepository,
                                AttachmentFilePath       $attachmentFilePathService,
                                private ReportRepository $reportRepository)
    {
        parent::__construct($attachmentRepository, $attachmentFilePathService);
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $params = $request->getParsedBody();

        $files = $request->getUploadedFiles();

        $resultFile = $files['resultFile'];

        $userId = $request->getAttribute('userId');

        $report = new Report();
        $report->projectId = 0;
        $report->generatedByUid = $userId;
        $report->versionName = $params['version_name'];
        $report->versionDescription = $params['version_description'];
        $this->logger->info('ccc');

        $report->is_template = true;

        $report->id = $this->reportRepository->insert($report);

        $attachment = $this->uploadAttachment($resultFile, 'report', $report->id, $userId);

        return ['success' => true];
    }
}
