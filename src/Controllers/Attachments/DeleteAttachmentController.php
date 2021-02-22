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
    public function __construct(private AttachmentFilePath $attachmentFilePathService)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $attachmentId = (int)$args['attachmentId'];

        $repository = new AttachmentRepository($this->db);
        $attachment = $repository->findById($attachmentId);

        $pathName = $this->attachmentFilePathService->generateFilePathFromAttachment((array)$attachment);
        if (unlink($pathName) === false) {
            $this->logger->warning('Unable to delete: ' . $pathName);
        }

        $success = $repository->deleteById($attachmentId);

        $userId = $request->getAttribute('userId');
        $this->auditAction($userId, $attachmentId);

        return ['success' => $success];
    }

    private function auditAction(int $loggedInUserId, int $attachmentId): void
    {
        $activityPublisherService = $this->container->get(ActivityPublisherService::class);
        $activityPublisherService->publish($loggedInUserId, AuditLogAction::ATTACHMENT_DELETED, ['id' => $attachmentId]);
    }
}
