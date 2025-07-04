<?php declare(strict_types=1);

namespace Reconmap\Controllers\Attachments;

use OpenApi\Attributes as OpenApi;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Http\Docs\Default204NoContentResponse;
use Reconmap\Http\Docs\Default403UnauthorisedResponse;
use Reconmap\Http\Docs\Default404NotFoundResponse;
use Reconmap\Http\Docs\InPathIdParameter;
use Reconmap\Models\AuditActions\AuditActions;
use Reconmap\Repositories\AttachmentRepository;
use Reconmap\Services\ActivityPublisherService;
use Reconmap\Services\Filesystem\AttachmentFilePath;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

#[OpenApi\Delete(path: "/attachments/{attachmentId}", description: "Deletes attachment with the given id", security: ["bearerAuth"], tags: ["Attachments"],
    parameters: [new InPathIdParameter("attachmentId")])]
#[Default204NoContentResponse]
#[Default403UnauthorisedResponse]
#[Default404NotFoundResponse]
class DeleteAttachmentController extends Controller
{
    public function __construct(private readonly AttachmentRepository     $attachmentRepository,
                                private readonly AttachmentFilePath       $attachmentFilePathService,
                                private readonly ActivityPublisherService $activityPublisherService,
                                private readonly Filesystem               $filesystem)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $attachmentId = (int)$args['attachmentId'];

        $attachment = $this->attachmentRepository->findById($attachmentId);
        if (empty($attachment)) {
            return $this->createNotFoundResponse();
        }

        $pathName = $this->attachmentFilePathService->generateFilePathFromAttachment((array)$attachment);

        try {
            $this->filesystem->remove($pathName);
        } catch (IOException $ioe) {
            $this->logger->warning($ioe->getMessage());
        }

        $this->attachmentRepository->deleteById($attachmentId);

        $userId = $request->getAttribute('userId');
        $this->auditAction($userId, $attachmentId);

        return $this->createDeletedResponse();
    }

    private function auditAction(int $loggedInUserId, int $attachmentId): void
    {
        $this->activityPublisherService->publish($loggedInUserId, AuditActions::DELETED, 'Attachment', ['id' => $attachmentId]);
    }
}
