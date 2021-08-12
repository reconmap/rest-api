<?php declare(strict_types=1);

namespace Reconmap\Controllers\Attachments;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Reconmap\ControllerTestCase;
use Reconmap\Models\Attachment;
use Reconmap\Repositories\AttachmentRepository;
use Reconmap\Services\AttachmentFilePath;

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
            ->willReturn(1);

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

        $args = ['attachmentId' => $fakeAttachmentId];

        $controller = $this->injectController(new UploadAttachmentController($mockAttachmentRepository, $mockAttachmentFilePath));
        $response = $controller($mockRequest, $args);

        $this->assertTrue($response['success']);
    }
}
