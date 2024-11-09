<?php declare(strict_types=1);

namespace Reconmap\Controllers\Attachments;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Models\Attachment;
use Reconmap\Models\AuditActions\AttachmentAuditActions;
use Reconmap\Repositories\AttachmentRepository;
use Reconmap\Services\ActivityPublisherService;
use Reconmap\Services\Filesystem\AttachmentFilePath;
use Symfony\Component\Filesystem\Filesystem;

class DeleteAttachmentControllerTest extends TestCase
{
    public function testSuccessfulDelete()
    {
        $fakeAttachmentId = 86;

        $mockAttachmentRepository = $this->createMock(AttachmentRepository::class);
        $mockAttachmentRepository->expects($this->once())
            ->method('findById')
            ->with($fakeAttachmentId)
            ->willReturn(new Attachment());
        $mockAttachmentRepository->expects($this->once())
            ->method('deleteById')
            ->with($fakeAttachmentId)
            ->willReturn(true);

        $mockAttachmentFilePath = $this->createMock(AttachmentFilePath::class);
        $mockAttachmentFilePath->expects($this->once())
            ->method('generateFilePathFromAttachment')
            ->willReturn('/fake/path/to/attachment');

        $mockPublisherService = $this->createMock(ActivityPublisherService::class);
        $mockPublisherService->expects($this->once())
            ->method('publish')
            ->with(9, AttachmentAuditActions::ATTACHMENT_DELETED, ['type' => 'attachment', 'id' => $fakeAttachmentId]);

        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->once())
            ->method('getAttribute')
            ->with('userId')
            ->willReturn(9);

        $mockFilesystem = $this->createMock(Filesystem::class);
        $mockFilesystem->expects($this->once())
            ->method('remove')
            ->with('/fake/path/to/attachment');

        $args = ['attachmentId' => $fakeAttachmentId];

        $controller = new DeleteAttachmentController($mockAttachmentRepository, $mockAttachmentFilePath, $mockPublisherService, $mockFilesystem);
        $response = $controller($mockRequest, $args);

        $this->assertEquals(\Symfony\Component\HttpFoundation\Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }
}
