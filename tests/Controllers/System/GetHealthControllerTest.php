<?php declare(strict_types=1);

namespace Reconmap\Controllers\System;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Services\Filesystem\ApplicationLogFilePath;
use Reconmap\Services\Filesystem\AttachmentFilePath;
use Reconmap\Services\Filesystem\DirectoryChecker;

class GetHealthControllerTest extends TestCase
{
    public function testResponse()
    {
        $mockAttachmentFilePath = $this->createMock(AttachmentFilePath::class);
        $mockAttachmentFilePath->expects($this->once())
            ->method('generateBasePath')
            ->willReturn('/i/am/not/a/dir');
        $mockServerRequestInterface = $this->createMock(ServerRequestInterface::class);

        $mockApplicationLogFilePath = $this->createMock(ApplicationLogFilePath::class);
        $mockDirectoryChecker = $this->createMock(DirectoryChecker::class);
        $mockDirectoryChecker->expects($this->exactly(2))
            ->method('checkDirectoryIsWriteable')
            ->willReturn([
                'location' => '/i/am/not/a/dir',
                'exists' => false,
                'writeable' => false
            ]);
        $mockMysql = $this->createMock(\mysqli::class);
        $mockMysql->expects($this->once())
            ->method('ping')
            ->willReturn(true);

        $controller = new GetHealthController($mockAttachmentFilePath, $mockApplicationLogFilePath, $mockDirectoryChecker, $mockMysql);
        $response = $controller($mockServerRequestInterface);
        $expectedResponse = [
            'attachmentsDirectory' => [
                'location' => '/i/am/not/a/dir',
                'exists' => false,
                'writeable' => false
            ],
            'logsDirectory' => [
                'location' => '/i/am/not/a/dir',
                'exists' => false,
                'writeable' => false
            ],
            'dbConnection' => ['ping' => true]
        ];
        $this->assertEquals($expectedResponse, $response);
    }
}
