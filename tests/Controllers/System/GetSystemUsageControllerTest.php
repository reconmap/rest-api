<?php declare(strict_types=1);

namespace Reconmap\Controllers\System;

use GuzzleHttp\Psr7\ServerRequest;
use Reconmap\ControllerTestCase;
use Reconmap\Repositories\AttachmentRepository;

class GetSystemUsageControllerTest extends ControllerTestCase
{
    public function testAttachmentsUsage()
    {
        $mockRepository = $this->createPartialMock(AttachmentRepository::class, ['getUsage']);
        $mockRepository->expects($this->once())
            ->method('getUsage')
            ->willReturn(['total_count' => 1, 'total_file_size' => 125984]);

        $request = new ServerRequest('get', '/system/usage');

        /** @var $controller GetSystemUsageController */
        $controller = $this->injectController(new GetSystemUsageController($mockRepository));
        $response = $controller($request);

        $this->assertEquals(['attachments' => ['total_count' => 1, 'total_file_size' => 125984]], $response);
    }
}
