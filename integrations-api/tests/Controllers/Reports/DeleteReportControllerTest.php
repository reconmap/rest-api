<?php declare(strict_types=1);

namespace Reconmap\Controllers\Reports;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Models\Attachment;
use Reconmap\Repositories\AttachmentRepository;
use Reconmap\Repositories\ReportRepository;
use Reconmap\Services\Filesystem\AttachmentFilePath;
use Symfony\Component\Filesystem\Filesystem;

class DeleteReportControllerTest extends TestCase
{
    public function testSuccessfulDelete()
    {
        $fakeReportId = 86;

        $fakeAttachment = new Attachment();
        $fakeAttachment->file_name = 'foo.bar';

        $mockAttachmentRepository = $this->createMock(AttachmentRepository::class);
        $mockAttachmentRepository->expects($this->once())
            ->method('findByParentId')
            ->with('report', $fakeReportId)
            ->willReturn([(array)$fakeAttachment]);

        $mockReportRepository = $this->createMock(ReportRepository::class);
        $mockReportRepository->expects($this->once())
            ->method('deleteById')
            ->with($fakeReportId)
            ->willReturn(true);

        $mockAttachmentFilePath = $this->createMock(AttachmentFilePath::class);
        $mockAttachmentFilePath->expects($this->once())
            ->method('generateFilePath')
            ->with('foo.bar')
            ->willReturn('/fake/path/to/attachment/foo.bar');

        $mockRequest = $this->createMock(ServerRequestInterface::class);

        $mockFilesystem = $this->createMock(Filesystem::class);
        $mockFilesystem->expects($this->once())
            ->method('remove')
            ->with('/fake/path/to/attachment/foo.bar');

        $args = ['reportId' => $fakeReportId];

        $controller = new DeleteReportController($mockAttachmentFilePath, $mockReportRepository, $mockAttachmentRepository, $mockFilesystem);
        $response = $controller($mockRequest, $args);

        $this->assertEquals(204, $response->getStatusCode());
    }
}
