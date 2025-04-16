<?php declare(strict_types=1);

namespace Reconmap\Controllers\System;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Database\MysqlServer;
use Reconmap\Services\Filesystem\AttachmentFilePath;
use Reconmap\Services\Filesystem\DirectoryChecker;
use Reconmap\Services\RedisServer;

class GetHealthControllerTest extends TestCase
{
    public function testResponse()
    {
        $mockAttachmentFilePath = $this->createMock(AttachmentFilePath::class);
        $mockAttachmentFilePath->expects($this->once())
            ->method('generateBasePath')
            ->willReturn('/i/am/not/a/dir');
        $mockServerRequestInterface = $this->createMock(ServerRequestInterface::class);

        $mockDirectoryChecker = $this->createMock(DirectoryChecker::class);
        $mockDirectoryChecker->expects($this->once(2))
            ->method('checkDirectoryIsWriteable')
            ->willReturn([
                'location' => '/i/am/not/a/dir',
                'exists' => false,
                'writeable' => false
            ]);
        $mockMysql = $this->createMock(MysqlServer::class);
        $mockMysql->expects($this->once())
            ->method('tryDummyQuery')
            ->willReturn(true);

        $mockRedis = $this->createMock(RedisServer::class);
        $mockRedis->expects($this->once())
            ->method('ping')
            ->willReturn(false);

        $controller = new GetHealthController($mockAttachmentFilePath, $mockDirectoryChecker, $mockMysql, $mockRedis);
        $response = $controller($mockServerRequestInterface);
        $expectedResponse = [
            'attachmentsDirectory' => [
                'location' => '/i/am/not/a/dir',
                'exists' => false,
                'writeable' => false
            ],
            'databaseServer' => ['reachable' => true],
            'keyValueServer' => ['reachable' => false],
        ];
        $this->assertEquals($expectedResponse, $response);
    }
}
