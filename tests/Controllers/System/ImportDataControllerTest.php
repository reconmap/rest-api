<?php declare(strict_types=1);

namespace Reconmap\Controllers\System;

use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Reconmap\Repositories\Importers\ProjectsImporter;
use Reconmap\Services\AuditLogService;

class ImportDataControllerTest extends TestCase
{
    public function testEmptyFileReturnsError()
    {
        $mockLogger = $this->createMock(Logger::class);

        $mockUploadedFileInterface = $this->createMock(UploadedFileInterface::class);
        $mockUploadedFileInterface->expects($this->once())
            ->method('getSize')
            ->willReturn(0);

        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->once())
            ->method('getUploadedFiles')
            ->willReturn(['importFile' => $mockUploadedFileInterface]);
        $mockRequest->expects($this->never())
            ->method('getAttribute');

        $mockAuditLogService = $this->createMock(AuditLogService::class);

        $mockProjectImporter = $this->createMock(ProjectsImporter::class);
        $mockProjectImporter->expects($this->never())
            ->method('import');

        $mockContainer = $this->createMock(ContainerInterface::class);
        $mockContainer->expects($this->never())
            ->method('get');

        $controller = new ImportDataController($mockAuditLogService, $mockContainer);
        $controller->setLogger($mockLogger);
        $response = $controller($mockRequest);

        $expectedResponse = [
            'results' => [],
            'errors' => ['Uploaded file is empty.'],
        ];
        $this->assertEquals($expectedResponse, $response);
    }

    public function testInvalidJsonReturnsError()
    {
        $mockLogger = $this->createMock(Logger::class);

        $body = '¯\_(ツ)_/¯';
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
        $mockRequest->expects($this->never())
            ->method('getAttribute');

        $mockAuditLogService = $this->createMock(AuditLogService::class);

        $mockProjectImporter = $this->createMock(ProjectsImporter::class);
        $mockProjectImporter->expects($this->never())
            ->method('import');

        $mockContainer = $this->createMock(ContainerInterface::class);
        $mockContainer->expects($this->never())
            ->method('get');

        $controller = new ImportDataController($mockAuditLogService);
        $controller->setContainer($mockContainer);
        $controller->setLogger($mockLogger);
        $response = $controller($mockRequest);

        $expectedResponse = [
            'results' => [],
            'errors' => ['Invalid JSON file.'],
        ];
        $this->assertEquals($expectedResponse, $response);
    }

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
            ->willReturn(['count' => 0, 'errors' => []]);

        $mockContainer = $this->createMock(ContainerInterface::class);
        $mockContainer->expects($this->once())
            ->method('get')
            ->with(ProjectsImporter::class)
            ->willReturn($mockProjectImporter);

        $controller = new ImportDataController($mockAuditLogService);
        $controller->setContainer($mockContainer);
        $response = $controller($mockRequest);

        $expectedResponse = [
            'results' => [
                [
                    'name' => 'projects',
                    'count' => 0,
                    'errors' => [],
                ]
            ],
            'errors' => [],
        ];
        $this->assertEquals($expectedResponse, $response);
    }
}
