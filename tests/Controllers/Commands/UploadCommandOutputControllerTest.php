<?php declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Reconmap\ControllerTestCase;
use Reconmap\Models\Attachment;
use Reconmap\Repositories\AttachmentRepository;
use Reconmap\Repositories\CommandRepository;
use Reconmap\Services\Filesystem\AttachmentFilePath;
use Reconmap\Services\RedisServer;

class UploadCommandOutputControllerTest extends ControllerTestCase
{
    public function testHappyPath()
    {
        $fakeAttachmentId = 86;

        $fakeAttachment = new Attachment();
        $fakeAttachment->client_file_name = 'something.jpeg';
        $fakeAttachment->file_mimetype = 'image/jpeg';

        $mockAttachmentRepository = $this->createMock(AttachmentRepository::class);
        $mockAttachmentRepository->expects($this->once())
            ->method('insert')
            ->with($this->isInstanceOf(Attachment::class))
            ->willReturn($fakeAttachmentId);

        $mockAttachmentFilePath = $this->createMock(AttachmentFilePath::class);
        $mockAttachmentFilePath->expects($this->atLeast(1))
            ->method('generateFilePath')
            ->willReturn(__FILE__);
        $mockAttachmentFilePath->expects($this->once())
            ->method('generateBasePath')
            ->willReturn(__DIR__);

        $fakeUploadedFile = $this->createMock(UploadedFileInterface::class);
        $fakeUploadedFile->expects($this->exactly(1))
            ->method('getClientFilename')
            ->willReturn('something.jpeg');
        $fakeUploadedFile->expects($this->once())
            ->method('getError')
            ->willReturn(UPLOAD_ERR_OK);


        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->once())
            ->method('getAttribute')
            ->with('userId')
            ->willReturn(9);
        $mockRequest->expects($this->once())
            ->method('getParsedBody')
            ->willReturn(['parentType' => 'task', 'parentId' => 10, 'commandId' => 5]);
        $mockRequest->expects($this->once())
            ->method('getUploadedFiles')
            ->willReturn(['resultFile' => $fakeUploadedFile]);

        $mockRedisServer = $this->createMock(RedisServer::class);

        $mockCommandRepository = $this->createMock(CommandRepository::class);
        $mockCommandRepository->expects($this->once())
            ->method('findById')
            ->willReturn(['id' => 5]);

        $args = ['attachmentId' => $fakeAttachmentId];

        $controller = $this->injectController(new UploadCommandOutputController($mockAttachmentRepository, $mockAttachmentFilePath, $mockRedisServer, $mockCommandRepository));
        $response = $controller($mockRequest, $args);

        $this->assertTrue($response['success']);
    }
}
