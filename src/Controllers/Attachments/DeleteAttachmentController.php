<?php declare(strict_types=1);

namespace Reconmap\Controllers\Attachments;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditLogAction;
use Reconmap\Repositories\AttachmentRepository;
use Reconmap\Services\ActivityPublisherService;
use Reconmap\Services\AttachmentFilePath;

class DeleteAttachmentController extends Controller
{
    public function __construct(private AttachmentRepository $attachmentRepository,
                                private AttachmentFilePath $attachmentFilePathService,
                                private ActivityPublisherService $activityPublisherService)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $attachmentId = (int)$args['attachmentId'];

        $attachment = $this->attachmentRepository->findById($attachmentId);

        $pathName = $this->attachmentFilePathService->generateFilePathFromAttachment((array)$attachment);
        if (unlink($pathName) === false) {
            $this->logger->warning('Unable to delete: ' . $pathName);
        }

        $success = $this->attachmentRepository->deleteById($attachmentId);

        $userId = $request->getAttribute('userId');
        $this->auditAction($userId, $attachmentId);

        return ['success' => $success];
    }

    private function auditAction(int $loggedInUserId, int $attachmentId): void
    {
        $this->activityPublisherService->publish($loggedInUserId, AuditLogAction::ATTACHMENT_DELETED, ['id' => $attachmentId]);
    }
}
