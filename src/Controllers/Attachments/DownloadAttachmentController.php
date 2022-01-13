<?php declare(strict_types=1);

namespace Reconmap\Controllers\Attachments;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditActions\AuditLogAction;
use Reconmap\Repositories\AttachmentRepository;
use Reconmap\Services\AuditLogService;
use Reconmap\Services\Filesystem\AttachmentFilePath;

class DownloadAttachmentController extends Controller
{
    public function __construct(private AttachmentRepository $attachmentRepository,
                                private AttachmentFilePath   $attachmentFilePathService,
                                private AuditLogService      $auditLogService)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $attachmentId = (int)$args['attachmentId'];

        $attachment = $this->attachmentRepository->findById($attachmentId);

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
        $this->auditLogService->insert($loggedInUserId, AuditLogAction::ATTACHMENT_DOWNLOADED, [$fileName]);
    }
}
