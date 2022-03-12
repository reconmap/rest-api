<?php declare(strict_types=1);

namespace Reconmap\Controllers\Reports;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Reconmap\Repositories\AttachmentRepository;
use Reconmap\Repositories\ReportRepository;
use Reconmap\Services\Filesystem\AttachmentFilePath;
use Reconmap\Services\RedisServer;

class CreateReportTemplateControllerTest extends TestCase
{
    public function testHappyPath()
    {
        $mockAttachmentRepository = $this->createMock(AttachmentRepository::class);
        $mockAttachmentFilePath = $this->createMock(AttachmentFilePath::class);
        $mockAttachmentFilePath->expects($this->once())
            ->method('generateFilePath')
            ->willReturn(__FILE__);
        $mockRedisServer = $this->createMock(RedisServer::class);
        $mockReportRepository = $this->createMock(ReportRepository::class);

        $mockFile = $this->createMock(UploadedFileInterface::class);
        $mockFile->expects($this->once())
            ->method('getClientFilename')
            ->willReturn('steganography.png');

        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->once())
            ->method('getParsedBody')
            ->willReturn([
                'version_name' => '1',
                'version_description' => '2'
            ]);
        $mockRequest->expects($this->once())
            ->method('getAttribute')
            ->with('userId')
            ->willReturn(1);
        $mockRequest->expects($this->once())
            ->method('getUploadedFiles')
            ->willReturn(['resultFile' => $mockFile]);

        $controller = new CreateReportTemplateController($mockAttachmentRepository, $mockAttachmentFilePath, $mockRedisServer, $mockReportRepository);
        $response = $controller($mockRequest, []);
        $this->assertEquals(['success' => true, 'attachmentId' => 0], $response);
    }
}
