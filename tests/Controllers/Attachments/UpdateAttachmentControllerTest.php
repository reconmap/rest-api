<?php declare(strict_types=1);

namespace Reconmap\Controllers\Attachments;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Reconmap\ControllerTestCase;
use Reconmap\Models\Attachment;
use Reconmap\Repositories\AttachmentRepository;
use Reconmap\Services\Filesystem\AttachmentFilePath;
use Reconmap\Services\AuditLogService;
use Reconmap\Models\AuditActions\AuditLogAction;

class UpdateAttachmentControllerTest extends ControllerTestCase
{
    public function testHappyPath()
    {
        $fakeAttachmentId = 86;

        $fakeAttachment = new Attachment();
        $fakeAttachment->client_file_name = 'something.jpeg';

        $mockAttachmentRepository = $this->createMock(AttachmentRepository::class);
        $mockAttachmentRepository->expects($this->once())
            ->method('updateById')
            ->willReturn(true);
        $mockAttachmentRepository->expects($this->once())
            ->method('getFileNameById')
            ->willReturn('6498a0fff3bb17792efe446b098d0c95');

        $mockAttachmentFilePath = $this->createMock(AttachmentFilePath::class);
        $mockAttachmentFilePath->expects($this->once())
            ->method('generateFilePath')
            ->willReturn(__FILE__);
        $mockAttachmentFilePath->expects($this->once())
            ->method('generateBasePath')
            ->willReturn(__DIR__);

        $mockAuditLogService = $this->createMock(AuditLogService::class);
        $mockAuditLogService->expects($this->once())
            ->method('insert')
            ->with(9, AuditLogAction::ATTACHMENT_UPDATED, [$fakeAttachmentId]);

        $fakeUploadedFile = $this->createMock(UploadedFileInterface::class);
        $fakeUploadedFile->expects($this->exactly(2))
            ->method('getClientFilename')
            ->willReturn('something.jpeg');


        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->once())
            ->method('getAttribute')
            ->with('userId')
            ->willReturn(9);
        $mockRequest->expects($this->once())
            ->method('getParsedBody')
            ->willReturn(['parentType' => 'task', 'parentId' => 10, 'attachmentId' => $fakeAttachmentId]);
        $mockRequest->expects($this->once())
            ->method('getUploadedFiles')
            ->willReturn(['attachment' => [
                $fakeUploadedFile
            ]]);

        $args = ['attachmentId' => $fakeAttachmentId];

        $controller = $this->injectController(new UpdateAttachmentController($mockAttachmentRepository, $mockAttachmentFilePath, $mockAuditLogService));
        $response = $controller($mockRequest, $args);

        $this->assertTrue($response['success']);
    }
}
