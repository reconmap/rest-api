<?php declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\Attachment;
use Reconmap\Repositories\AttachmentRepository;
use Reconmap\Services\AttachmentFilePath;
use Reconmap\Services\RedisServer;

class UploadCommandOutputController extends Controller
{
    public function __construct(private AttachmentFilePath $attachmentFilePathService,
                                private AttachmentRepository $attachmentRepository,
                                private RedisServer $redisServer)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $params = $request->getParsedBody();
        $taskId = (int)$params['taskId'];

        $fileName = $this->attachmentFilePathService->generateFileName();
        $pathName = $this->attachmentFilePathService->generateFilePath($fileName);

        $files = $request->getUploadedFiles();

        $resultFile = $files['resultFile'];
        if ($resultFile->getError() === UPLOAD_ERR_OK) {
            $resultFile->moveTo($pathName);
        }

        $userId = $request->getAttribute('userId');

        $attachment = new Attachment();
        $attachment->parent_type = 'command';
        $attachment->parent_id = $taskId;
        $attachment->submitter_uid = $userId;
        $attachment->file_name = $fileName;
        $attachment->client_file_name = $resultFile->getClientFilename();
        $attachment->file_hash = hash_file('md5', $pathName);
        $attachment->file_size = filesize($pathName);
        $attachment->file_mimetype = mime_content_type($pathName);

        $this->attachmentRepository->insert($attachment);

        $result = $this->redisServer->lPush("tasks:queue",
            json_encode([
                'taskId' => $taskId,
                'userId' => $userId,
                'filePath' => $pathName
            ])
        );
        if (false === $result) {
            $this->logger->error('Item could not be pushed to the queue', ['queue' => 'tasks-results:queue']);
        }

        return ['success' => true];
    }
}
