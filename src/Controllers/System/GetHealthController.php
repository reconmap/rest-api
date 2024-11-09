<?php declare(strict_types=1);

namespace Reconmap\Controllers\System;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Services\Filesystem\AttachmentFilePath;
use Reconmap\Services\Filesystem\DirectoryChecker;
use Reconmap\Services\RedisServer;

class GetHealthController extends Controller
{
    public function __construct(
        private readonly AttachmentFilePath $attachmentFilePath,
        private readonly DirectoryChecker   $directoryChecker,
        private readonly \mysqli            $mysqlServer,
        private readonly RedisServer        $redisServer,
    )
    {
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        $attachmentBasePath = $this->attachmentFilePath->generateBasePath();

        return [
            'attachmentsDirectory' => $this->directoryChecker->checkDirectoryIsWriteable($attachmentBasePath),
            'databaseServer' => [
                'reachable' => $this->mysqlServer->ping()
            ],
            'keyValueServer' => [
                'reachable' => $this->isRedisServerReachable()
            ]
        ];
    }

    private function isRedisServerReachable(): bool
    {
        try {
            return $this->redisServer->ping();
        } catch (\RedisException $e) {
            $this->logger()->warning($e->getMessage());
            return false;
        }
    }
}
