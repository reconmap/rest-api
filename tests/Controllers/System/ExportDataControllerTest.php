<?php declare(strict_types=1);

namespace Reconmap\Controllers\System;

use GuzzleHttp\Psr7\ServerRequest;
use Psr\Container\ContainerInterface;
use Reconmap\ControllerTestCase;
use Reconmap\Repositories\Exporters\ClientsExporter;
use Reconmap\Services\AuditLogService;

class ExportDataControllerTest extends ControllerTestCase
{
    public function testExport()
    {
        $mockAuditLogService = $this->createPartialMock(AuditLogService::class, ['insert']);
        $mockAuditLogService->expects($this->once())
            ->method('insert')
            ->with(6, 'Exported data', ['clients']);
        $request = (new ServerRequest('get', '/system/data'))
            ->withAttribute('userId', 6)
            ->withQueryParams([
                'entities' => 'clients'
            ]);

        $mockClientExporter = $this->createMock(ClientsExporter::class);
        $mockClientExporter->expects($this->once())
            ->method('export')
            ->willReturn(['client1']);

        $mockContainer = $this->createMock(ContainerInterface::class);
        $mockContainer->expects($this->once())
            ->method('get')
            ->with('Reconmap\Repositories\Exporters\ClientsExporter')
            ->willReturn($mockClientExporter);

        /** @var $controller ExportDataController */
        $controller = $this->injectController(new ExportDataController($mockAuditLogService));
        $controller->setContainer($mockContainer);
        $response = $controller($request);

        $this->assertEquals(\Symfony\Component\HttpFoundation\Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals('{"clients":["client1"]}', $response->getBody()->getContents());
        $this->assertStringContainsString('attachment; filename="reconmap-clients-', $response->getHeaderLine('Content-Disposition'));
        $this->assertEquals('application/json; charset=UTF-8', $response->getHeaderLine('Content-Type'));
    }

    public function testExportInvalidEntities()
    {
        $mockAuditLogService = $this->createPartialMock(AuditLogService::class, ['insert']);
        $mockAuditLogService->expects($this->never())
            ->method('insert');
        $request = (new ServerRequest('get', '/system/data'))
            ->withAttribute('userId', 6)
            ->withQueryParams([
                'entities' => 'passwords'
            ]);

        $mockContainer = $this->createMock(ContainerInterface::class);

        /** @var $controller ExportDataController */
        $controller = $this->injectController(new ExportDataController($mockAuditLogService));
        $controller->setContainer($mockContainer);
        $response = $controller($request);

        $this->assertEquals(\Symfony\Component\HttpFoundation\Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }
}
