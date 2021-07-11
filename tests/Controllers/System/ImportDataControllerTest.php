<?php declare(strict_types=1);

namespace Reconmap\Controllers\System;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Reconmap\Repositories\Importers\ProjectsImporter;
use Reconmap\Services\AuditLogService;

class ImportDataControllerTest extends TestCase
{
    public function testProjectImport()
    {
        $body = '{"projects":[]}';
        $mockStreamInterface = $this->createMock(StreamInterface::class);
        $mockStreamInterface->expects($this->once())
            ->method('getContents')
            ->willReturn($body);

        $mockUploadedFileInterface = $this->createMock(UploadedFileInterface::class);
        $mockUploadedFileInterface->expects($this->once())
            ->method('getStream')
            ->willReturn($mockStreamInterface);

        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->once())
            ->method('getUploadedFiles')
            ->willReturn(['importFile' => $mockUploadedFileInterface]);
        $mockRequest->expects($this->once())
            ->method('getAttribute')
            ->with('userId')
            ->willReturn(9);

        $mockAuditLogService = $this->createMock(AuditLogService::class);

        $mockProjectImporter = $this->createMock(ProjectsImporter::class);
        $mockProjectImporter->expects($this->once())
            ->method('import')
            ->willReturn([[]]);

        $mockContainer = $this->createMock(ContainerInterface::class);
        $mockContainer->expects($this->once())
            ->method('get')
            ->with('Reconmap\Repositories\Importers\ProjectsImporter')
            ->willReturn($mockProjectImporter);

        $controller = new ImportDataController($mockAuditLogService);
        $controller->setContainer($mockContainer);
        $response = $controller($mockRequest);

        $this->assertEquals([['name' => 'projects', 0 => []]], $response);
    }
}
