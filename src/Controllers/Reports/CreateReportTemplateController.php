<?php declare(strict_types=1);

namespace Reconmap\Controllers\Reports;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Attachments\UploadAttachmentController;
use Reconmap\Models\Report;
use Reconmap\Repositories\AttachmentRepository;
use Reconmap\Repositories\ReportRepository;
use Reconmap\Services\Filesystem\AttachmentFilePath;
use Reconmap\Services\RedisServer;

class CreateReportTemplateController extends UploadAttachmentController
{
    public function __construct(AttachmentRepository $attachmentRepository,
                                AttachmentFilePath   $attachmentFilePathService,
                                RedisServer          $redisServer,
                                private              readonly ReportRepository $reportRepository
    )
    {
        parent::__construct($attachmentRepository, $attachmentFilePathService, $redisServer);
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $params = $request->getParsedBody();

        $files = $request->getUploadedFiles();

        $resultFile = $files['resultFile'];

        $userId = $request->getAttribute('userId');

        $report = new Report();
        $report->projectId = null;
        $report->generatedByUid = $userId;
        $report->versionName = $params['version_name'];
        $report->versionDescription = $params['version_description'];

        $report->is_template = true;

        $report->id = $this->reportRepository->insert($report);

        $attachment = $this->uploadAttachment($resultFile, 'report', $report->id, $userId);

        return ['success' => true, 'attachmentId' => $attachment->id];
    }
}
