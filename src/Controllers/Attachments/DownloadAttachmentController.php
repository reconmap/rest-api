<?php declare(strict_types=1);

namespace Reconmap\Controllers\Attachments;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditLogAction;
use Reconmap\Repositories\AttachmentRepository;
use Reconmap\Services\AuditLogService;

class DownloadAttachmentController extends Controller
{

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $attachmentId = (int)$args['attachmentId'];

        $repository = new AttachmentRepository($this->db);
        $attachment = $repository->findById($attachmentId);

        $userId = $request->getAttribute('userId');

        $pathName = RECONMAP_APP_DIR . '/data/attachments/' . $attachment->file_name;

        $response = new Response;

        $this->auditAction($userId, $attachment->client_file_name);

        return $response
            ->withHeader('Access-Control-Expose-Headers', 'Content-Disposition')
            ->withHeader('Content-Disposition', 'attachment; filename="' . $attachment->client_file_name . '";')
            ->withHeader('Content-type', $attachment->file_mimetype)
            ->withBody(new Stream(fopen($pathName, 'r')));
    }

    private function auditAction(int $loggedInUserId, string $fileName): void
    {
        $auditLogService = new AuditLogService($this->db);
        $auditLogService->insert($loggedInUserId, AuditLogAction::ATTACHMENT_DOWNLOADED, [$fileName]);
    }
}
