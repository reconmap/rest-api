<?php declare(strict_types=1);

namespace Reconmap\Controllers\Attachments;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\Attachment;
use Reconmap\Repositories\AttachmentRepository;
use Reconmap\Services\Filesystem\AttachmentFilePath;

class UploadAttachmentController extends Controller
{
    public function __construct(protected readonly AttachmentRepository $attachmentRepository,
                                protected readonly AttachmentFilePath $attachmentFilePathService)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $params = $request->getParsedBody();
        $parentType = $params['parentType'];
        $parentId = (int)$params['parentId'];

        $userId = $request->getAttribute('userId');
        $files = $request->getUploadedFiles()['attachment'];

        $result = ['success' => true];
        $index = 0;
        foreach ($files as $file) {
            /** @var UploadedFileInterface $file */
            $this->logger->debug('file uploaded', ['filename' => $file->getClientFilename(), 'type' => $file->getClientMediaType(), 'size' => $file->getSize()]);
            $attachment = $this->uploadAttachment($file, $parentType, $parentId, $userId);
            $result[$index] = ["id" => $attachment->id];
            $index = $index + 1;
        }

        return $result;
    }

    /**
     * @throws \Exception
     */
    protected function uploadAttachment(UploadedFileInterface $uploadedFile, string $parentType, int $parentId, int $userId): Attachment
    {
        $fileName = $this->attachmentFilePathService->generateFileName();
        $pathName = $this->attachmentFilePathService->generateFilePath($fileName);
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

        $attachment = new Attachment();
        $attachment->parent_type = $parentType;
        $attachment->parent_id = $parentId;
        $attachment->submitter_uid = $userId;
        $attachment->client_file_name = $uploadedFile->getClientFilename();
        $attachment->file_name = $fileName;
        $attachment->file_hash = hash_file('md5', $pathName);
        $attachment->file_size = filesize($pathName);
        $attachment->file_mimetype = mime_content_type($pathName);

        $attachment->id = $this->attachmentRepository->insert($attachment);

        return $attachment;
    }
}
