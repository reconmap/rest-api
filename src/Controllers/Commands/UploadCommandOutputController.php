<?php declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Attachments\UploadAttachmentController;
use Reconmap\Repositories\AttachmentRepository;
use Reconmap\Services\AttachmentFilePath;
use Reconmap\Services\RedisServer;

class UploadCommandOutputController extends UploadAttachmentController
{
    public function __construct(AttachmentRepository $attachmentRepository,
                                AttachmentFilePath   $attachmentFilePathService,
                                private RedisServer  $redisServer)
    {
        parent::__construct($attachmentRepository, $attachmentFilePathService);
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $params = $request->getParsedBody();
        $taskId = (int)$params['taskId'];

        $files = $request->getUploadedFiles();

        $resultFile = $files['resultFile'];

        $userId = $request->getAttribute('userId');

        $attachment = $this->uploadAttachment($resultFile, 'command', $taskId, $userId);

        $result = $this->redisServer->lPush('tasks:queue',
            json_encode([
                'taskId' => $taskId,
                'userId' => $userId,
                'filePath' => $this->attachmentFilePathService->generateFilePath($attachment->file_name)
            ])
        );
        if (false === $result) {
            $this->logger->error('Item could not be pushed to the queue', ['queue' => 'tasks-results:queue']);
        }

        return ['success' => true];
    }
}
