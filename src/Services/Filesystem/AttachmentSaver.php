<?php

namespace Reconmap\Services\Filesystem;

use Psr\Http\Message\UploadedFileInterface;
use Reconmap\Models\Attachment;
use Reconmap\Repositories\AttachmentRepository;

class AttachmentSaver
{
    public function __construct(private readonly AttachmentFilePath   $attachmentFilePathService,
                                private readonly AttachmentRepository $attachmentRepository)
    {
    }

    public function uploadAttachment(UploadedFileInterface $uploadedFile, string $parentType, int $parentId, int $userId): Attachment
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
