<?php declare(strict_types=1);

namespace Reconmap\Controllers\System;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\AttachmentRepository;
use Reconmap\Services\RedisServer;

class GetSystemUsageController extends Controller
{
    public function __construct(private readonly AttachmentRepository $attachmentRepository, private readonly RedisServer $redisServer)
    {
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        return [
            'attachments' => $this->attachmentRepository->getUsage(),
            'queueLengths' => [
                'emails' => $this->redisServer->lLen('email:queue'),
                'tasks' => $this->redisServer->lLen('tasks:queue'),
                'notifications' => $this->redisServer->lLen('notifications:queue'),
            ]
        ];
    }
}
