<?php declare(strict_types=1);

namespace Reconmap\Controllers\Reports;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\AttachmentRepository;
use Reconmap\Services\AttachmentFilePath;
use Reconmap\Services\EmailService;

class SendReportController extends Controller
{
    public function __construct(private AttachmentFilePath $attachmentFilePathService,
                                private AttachmentRepository $attachmentRepository,
                                private EmailService $emailService)
    {
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        $deliverySettings = $this->getJsonBodyDecoded($request);
        $reportId = intval($deliverySettings->report_id);

        $attachments = $this->attachmentRepository->findByParentId('report', $reportId, 'application/pdf');

        if (count($attachments) === 0) {
            $this->logger->warning("Unable to find PDF for report $reportId");
        }

        $attachment = $attachments[0];

        $attachmentFilePath = $this->attachmentFilePathService->generateFilePathFromAttachment($attachment);

        $emailBody = $deliverySettings->body;
        $recipients = explode(',', $deliverySettings->recipients);
        $this->logger->debug('recipients', [$recipients]);

        $this->emailService->queueEmail($deliverySettings->subject, $recipients, $emailBody, $attachmentFilePath);

        return ['success' => true];
    }
}
