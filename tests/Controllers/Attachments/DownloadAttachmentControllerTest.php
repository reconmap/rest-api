<?php declare(strict_types=1);

namespace Reconmap\Controllers\Attachments;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Models\Attachment;
use Reconmap\Models\AuditActions\AttachmentAuditActions;
use Reconmap\Repositories\AttachmentRepository;
use Reconmap\Services\AuditLogService;
use Reconmap\Services\Filesystem\AttachmentFilePath;

class DownloadAttachmentControllerTest extends TestCase
{
    public function testHappyPath()
    {
        $fakeAttachmentId = 86;

        $fakeAttachment = new Attachment();
        $fakeAttachment->client_file_name = 'foo.jpg';
        $fakeAttachment->file_mimetype = 'image/jpeg';

        $mockAttachmentRepository = $this->createMock(AttachmentRepository::class);
        $mockAttachmentRepository->expects($this->once())
            ->method('findById')
            ->with($fakeAttachmentId)
            ->willReturn($fakeAttachment);

        $mockAttachmentFilePath = $this->createMock(AttachmentFilePath::class);
        $mockAttachmentFilePath->expects($this->once())
            ->method('generateFilePathFromAttachment')
            ->willReturn(__FILE__);

        $mockAuditLogService = $this->createMock(AuditLogService::class);
        $mockAuditLogService->expects($this->once())
            ->method('insert')
            ->with(9, AttachmentAuditActions::DOWNLOADED, 'Attachment', ['foo.jpg']);

        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->once())
            ->method('getAttribute')
            ->with('userId')
            ->willReturn(9);

        $args = ['attachmentId' => $fakeAttachmentId];

        $controller = new DownloadAttachmentController($mockAttachmentRepository, $mockAttachmentFilePath, $mockAuditLogService);
        $response = $controller($mockRequest, $args);

        $this->assertEquals('Content-Disposition', $response->getHeaderLine('Access-Control-Expose-Headers'));
    }
}
