<?php declare(strict_types=1);

namespace Reconmap\Controllers\Reports;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\AttachmentRepository;
use Reconmap\Services\AttachmentFilePath;
use Reconmap\Services\RedisServer;

class SendReportController extends Controller
{
    public function __construct(private AttachmentFilePath $attachmentFilePathService)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $deliverySettings = $this->getJsonBodyDecoded($request);
        $reportId = intval($deliverySettings->report_id);

        $attachmentRepository = new AttachmentRepository($this->db);
        $attachments = $attachmentRepository->findByParentId('report', $reportId, 'application/pdf');

        if (count($attachments) === 0) {
            $this->logger->warning("Unable to find PDF for report $reportId");
        }

        $attachment = $attachments[0];

        $attachmentFilePath = $this->attachmentFilePathService->generateFilePathFromAttachment($attachment);

        $emailBody = $deliverySettings->body;
        $recipients = explode(',', $deliverySettings->recipients);
        $this->logger->debug('recipients', [$recipients]);

        /** @var RedisServer $redis */
        $redis = $this->container->get(RedisServer::class);
        $result = $redis->lPush("email:queue",
            json_encode([
                'subject' => $deliverySettings->subject,
                'to' => $recipients,
                'body' => $emailBody,
                'attachmentPath' => $attachmentFilePath
            ])
        );
        if (false === $result) {
            $this->logger->error('Item could not be pushed to the queue', ['queue' => 'email:queue']);
        }

        return ['success' => true];
    }
}
