<?php declare(strict_types=1);

namespace Reconmap\Controllers\Reports;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\AttachmentRepository;
use Reconmap\Repositories\ReportRepository;
use Reconmap\Services\AttachmentFilePath;

class DeleteReportController extends Controller
{
    public function __construct(private AttachmentFilePath $attachmentFilePathService,
                                private ReportRepository $reportRepository,
                                private AttachmentRepository $attachmentRepository)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $reportId = (int)$args['reportId'];

        $success = $this->reportRepository->deleteById($reportId);

        $attachments = $this->attachmentRepository->findByParentId('report', $reportId);
        foreach ($attachments as $attachment) {
            $filePath = $this->attachmentFilePathService->generateFilePath($attachment['file_name']);

            if (unlink($filePath) === false) {
                $this->logger->warning("Unable to delete report file '$filePath'");
            }
        }

        return ['success' => $success];
    }
}
