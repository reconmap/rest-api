<?php declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Attachments\UploadAttachmentController;
use Reconmap\Repositories\AttachmentRepository;
use Reconmap\Repositories\CommandRepository;
use Reconmap\Services\Filesystem\AttachmentFilePath;
use Reconmap\Services\RedisServer;

class UploadCommandOutputController extends UploadAttachmentController
{
    public function __construct(AttachmentRepository $attachmentRepository,
                                AttachmentFilePath   $attachmentFilePathService,
                                RedisServer          $redisServer,
                                private              readonly CommandRepository $commandRepository,
    )
    {
        parent::__construct($attachmentRepository, $attachmentFilePathService, $redisServer);
    }


    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $params = $request->getParsedBody();
        $commandId = (int)$params['commandId'];
        $taskId = isset($params['taskId']) ? intval($params['taskId']) : null;

        $files = $request->getUploadedFiles();
        $resultFile = $files['resultFile'];

        $command = $this->commandRepository->findById($commandId);

        $userId = $request->getAttribute('userId');

        $attachment = $this->uploadAttachment($resultFile, 'command', $command['id'], $userId);

        $result = $this->redisServer->lPush('tasks:queue',
            json_encode([
                'commandId' => $command['id'],
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
