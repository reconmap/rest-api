<?php declare(strict_types=1);

namespace Reconmap\Controllers\Attachments;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditActions\AttachmentAuditActions;
use Reconmap\Repositories\AttachmentRepository;
use Reconmap\Services\AuditLogService;
use Reconmap\Services\Filesystem\AttachmentFilePath;

class DownloadAttachmentController extends Controller
{
    public function __construct(private readonly AttachmentRepository $attachmentRepository,
                                private readonly AttachmentFilePath $attachmentFilePathService,
                                private readonly AuditLogService $auditLogService)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $attachmentId = (int)$args['attachmentId'];

        $attachment = $this->attachmentRepository->findById($attachmentId);
        if (is_null($attachment)) {
            return $this->createNotFoundResponse();
        }

        $userId = $request->getAttribute('userId');

        $pathName = $this->attachmentFilePathService->generateFilePathFromAttachment((array)$attachment);

        $this->auditAction($userId, $attachment->client_file_name);

        $response = new Response;
        return $response
            ->withHeader('Access-Control-Expose-Headers', 'Content-Disposition')
            ->withHeader('Content-Disposition', 'attachment; filename="' . $attachment->client_file_name . '";')
            ->withHeader('Content-type', $attachment->file_mimetype)
            ->withBody(new Stream(fopen($pathName, 'r')));
    }

    private function auditAction(int $loggedInUserId, string $fileName): void
    {
        $this->auditLogService->insert($loggedInUserId, AttachmentAuditActions::ATTACHMENT_DOWNLOADED, [$fileName]);
    }
}
