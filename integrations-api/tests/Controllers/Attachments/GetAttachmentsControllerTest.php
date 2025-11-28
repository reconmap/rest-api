<?php declare(strict_types=1);

namespace Reconmap\Controllers\Attachments;

use GuzzleHttp\Psr7\ServerRequest;
use Reconmap\ControllerTestCase;
use Reconmap\Repositories\AttachmentRepository;

class GetAttachmentsControllerTest extends ControllerTestCase
{
    public function testResponse()
    {
        $mockRepository = $this->createPartialMock(AttachmentRepository::class, ['findByParentId']);
        $mockRepository->expects($this->once())
            ->method('findByParentId')
            ->with('project', 5)
            ->willReturn(['a1', 'a2']);
        $request = (new ServerRequest('get', '/attachments'))
            ->withQueryParams([
                'parentType' => 'project',
                'parentId' => 5
            ]);

        /**
         * @var $controller GetAttachmentsController
         */
        $controller = $this->injectController(new GetAttachmentsController($mockRepository));
        $response = $controller($request);
        $this->assertEquals(['a1', 'a2'], $response);
    }
}
