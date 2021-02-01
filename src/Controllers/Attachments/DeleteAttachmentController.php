<?php declare(strict_types=1);

namespace Reconmap\Controllers\Attachments;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditLogAction;
use Reconmap\Repositories\AttachmentRepository;
use Reconmap\Services\ActivityPublisherService;

class DeleteAttachmentController extends Controller
{

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $attachmentId = (int)$args['attachmentId'];

        $repository = new AttachmentRepository($this->db);
        $attachment = $repository->findById($attachmentId);

        $pathName = RECONMAP_APP_DIR . '/data/attachments/' . $attachment->file_name;
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
        $activityPublisherService->publish($loggedInUserId, AuditLogAction::COMMAND_OUTPUT_DELETED, ['type' => 'attachment', 'id' => $attachmentId]);
    }
}
