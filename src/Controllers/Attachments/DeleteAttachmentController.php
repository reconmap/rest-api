<?php declare(strict_types=1);

namespace Reconmap\Controllers\Attachments;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditActions\AuditLogAction;
use Reconmap\Repositories\AttachmentRepository;
use Reconmap\Services\ActivityPublisherService;
use Reconmap\Services\Filesystem\AttachmentFilePath;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class DeleteAttachmentController extends Controller
{
    public function __construct(private AttachmentRepository     $attachmentRepository,
                                private AttachmentFilePath       $attachmentFilePathService,
                                private ActivityPublisherService $activityPublisherService,
                                private Filesystem               $filesystem)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $attachmentId = (int)$args['attachmentId'];

        $attachment = $this->attachmentRepository->findById($attachmentId);

        $pathName = $this->attachmentFilePathService->generateFilePathFromAttachment((array)$attachment);

        try {
            $this->filesystem->remove($pathName);
        } catch (IOException $ioe) {
            $this->logger->warning($ioe->getMessage());
        }

        $success = $this->attachmentRepository->deleteById($attachmentId);

        $userId = $request->getAttribute('userId');
        $this->auditAction($userId, $attachmentId);

        return ['success' => $success];
    }

    private function auditAction(int $loggedInUserId, int $attachmentId): void
    {
        $this->activityPublisherService->publish($loggedInUserId, AuditLogAction::ATTACHMENT_DELETED, ['type' => 'attachment', 'id' => $attachmentId]);
    }
}
