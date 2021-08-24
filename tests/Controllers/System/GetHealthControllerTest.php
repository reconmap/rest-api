<?php declare(strict_types=1);

namespace Reconmap\Controllers\System;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Services\AttachmentFilePath;

class GetHealthControllerTest extends TestCase
{
    public function testResponse()
    {
        $mockAttachmentFilePath = $this->createMock(AttachmentFilePath::class);
        $mockAttachmentFilePath->expects($this->once())
            ->method('generateBasePath')
            ->willReturn('/i/am/not/a/dir');
        $mockServerRequestInterface = $this->createMock(ServerRequestInterface::class);
        $controller = new GetHealthController($mockAttachmentFilePath);
        $response = $controller($mockServerRequestInterface);
        $expectedResponse = [
            'attachmentsDirectory' => [
                'location' => '/i/am/not/a/dir',
                'exists' => false,
                'writeable' => false
            ]
        ];
        $this->assertEquals($expectedResponse, $response);
    }
}
