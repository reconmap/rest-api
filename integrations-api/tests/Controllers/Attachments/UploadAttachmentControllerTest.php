<?php declare(strict_types=1);

namespace Reconmap\Controllers\Attachments;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Log\LoggerInterface;
use Reconmap\ControllerTestCase;
use Reconmap\Models\Attachment;
use Reconmap\Repositories\AttachmentRepository;
use Reconmap\Services\Filesystem\AttachmentFilePath;
use Reconmap\Services\RedisServer;

class UploadAttachmentControllerTest extends ControllerTestCase
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
        $mockAttachmentFilePath->expects($this->once())
            ->method('generateFilePath')
            ->willReturn(__FILE__);
        $mockAttachmentFilePath->expects($this->once())
            ->method('generateBasePath')
            ->willReturn(__DIR__);

        $fakeUploadedFile = $this->createMock(UploadedFileInterface::class);
        $fakeUploadedFile->expects($this->exactly(2))
            ->method('getClientFilename')
            ->willReturn('something.jpeg');
        $fakeUploadedFile->expects($this->once())
            ->method('getClientMediaType')
            ->willReturn('image/jpg');
        $fakeUploadedFile->expects($this->once())
            ->method('getSize')
            ->willReturn(94145);
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
            ->willReturn(['parentType' => 'task', 'parentId' => 10]);
        $mockRequest->expects($this->once())
            ->method('getUploadedFiles')
            ->willReturn(['attachment' => [
                $fakeUploadedFile
            ]]);

        $mockRedisServer = $this->createMock(RedisServer::class);

        $args = ['attachmentId' => $fakeAttachmentId];

        $mockLogger = $this->createMock(LoggerInterface::class);

        $controller = $this->injectController(new UploadAttachmentController($mockAttachmentRepository, $mockAttachmentFilePath, $mockRedisServer));
        $controller->setLogger($mockLogger);
        $response = $controller($mockRequest, $args);

        $this->assertTrue($response['success']);
        $this->assertEquals($response[0]['id'], $fakeAttachmentId);
    }
}
