<?php declare(strict_types=1);

namespace Reconmap\Controllers\Attachments;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\Attachment;
use Reconmap\Repositories\AttachmentRepository;
use Reconmap\Services\Filesystem\AttachmentFilePath;
use Reconmap\Models\AuditActions\AuditLogAction;
use Reconmap\Services\AuditLogService;

class UpdateAttachmentController extends Controller
{
    public function __construct(protected AttachmentRepository $attachmentRepository,
                                protected AttachmentFilePath   $attachmentFilePathService,
                                private AuditLogService      $auditLogService)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $params = $request->getParsedBody();
        $parentType = $params['parentType'];
        $parentId = (int)$params['parentId'];

        $userId = $request->getAttribute('userId');
        $files = $request->getUploadedFiles()['attachment'];

        $attachmentId = (int)$params['attachmentId'];
        foreach ($files as $file) {
            /** @var UploadedFileInterface $file */
            $this->logger->debug('file updated', ['filename' => $file->getClientFilename(), 'type' => $file->getClientMediaType(), 'size' => $file->getSize()]);
            $this->updateAttachment($file, $parentType, $parentId, $userId, $attachmentId);
            $this->auditAction($userId, $attachmentId);
        }

        return ['success' => true];
    }

    /**
     * @throws \Exception
     */
    protected function updateAttachment(UploadedFileInterface $uploadedFile, string $parentType, int $parentId, int $userId, int $attachmentId): bool
    {
        $filename = $this->attachmentRepository->getFileNameById($attachmentId);
        $pathName = $this->attachmentFilePathService->generateFilePath($filename);
        $attachmentDir = $this->attachmentFilePathService->generateBasePath();

        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            if (!is_dir($attachmentDir)) {
                throw new \Exception('The attachments directory is missing: ' . $attachmentDir);
            }
            if (!is_writeable($attachmentDir)) {
                throw new \Exception('The attachments directory is not writeable: ' . $attachmentDir);
            }
            if (!file_exists($pathName) || is_writeable($pathName)) {
                $uploadedFile->moveTo($pathName);
            } else {
                throw new \Exception('Attachment file cannot be saved to ' . $pathName);
            }
        }

        $attachment = [
            "submitter_uid" => $userId,
            "client_file_name" => $uploadedFile->getClientFilename(),
            "file_hash" => hash_file('md5', $pathName),
            "file_size" => filesize($pathName),
            "file_mimetype" => mime_content_type($pathName)
        ];

        return $this->attachmentRepository->updateById($attachmentId, $attachment);
    }

    private function auditAction(int $loggedInUserId, int $attachmentId): void
    {
        $this->auditLogService->insert($loggedInUserId, AuditLogAction::ATTACHMENT_UPDATED, [$attachmentId]);
    }
}
