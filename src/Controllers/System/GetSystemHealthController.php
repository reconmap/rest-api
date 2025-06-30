<?php declare(strict_types=1);

namespace Reconmap\Controllers\System;

use OpenApi\Attributes as OpenApi;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Database\MysqlServer;
use Reconmap\Http\Docs\Default200OkResponse;
use Reconmap\Http\Docs\Default403UnauthorisedResponse;
use Reconmap\Services\Filesystem\AttachmentFilePath;
use Reconmap\Services\Filesystem\DirectoryChecker;
use Reconmap\Services\RedisServer;

#[OpenApi\Get(path: "/system/health", description: "Returns system health information", security: ["bearerAuth"], tags: ["System"])]
#[Default200OkResponse]
#[Default403UnauthorisedResponse]
class GetSystemHealthController extends Controller
{
    public function __construct(
        private readonly AttachmentFilePath $attachmentFilePath,
        private readonly DirectoryChecker   $directoryChecker,
        private readonly MysqlServer        $mysqlServer,
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
                'reachable' => $this->mysqlServer->tryDummyQuery()
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
            $this->logger->warning($e->getMessage());
            return false;
        }
    }
}
