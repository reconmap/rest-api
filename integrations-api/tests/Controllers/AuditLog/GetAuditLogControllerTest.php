<?php declare(strict_types=1);

namespace Reconmap\Controllers\AuditLog;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\ControllerTestCase;
use Reconmap\Repositories\AuditLogRepository;
use Reconmap\Services\ApplicationConfig;

class GetAuditLogControllerTest extends ControllerTestCase
{
    public function testResponse()
    {
        $mockRepository = $this->createPartialMock(AuditLogRepository::class, ['findAll', 'countAll']);
        $mockRepository->expects($this->once())
            ->method('findAll')
            ->with(1);

        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->once())
            ->method('getAttribute')
            ->with('role')
            ->willReturn('administrator');
        $mockRequest->expects($this->exactly(2))
            ->method('getQueryParams')
            ->willReturn(['page' => 1]);

        $mockConfig = $this->createMock(ApplicationConfig::class);
        $mockConfig->expects($this->once())
            ->method('offsetGet')
            ->willReturn([]);

        $mockAuthorisationService = $this->createAuthorisationServiceMock();

        /**
         * @var $controller GetAuditLogController
         */
        $controller = $this->injectController(new GetAuditLogController($mockAuthorisationService, $mockRepository, $mockConfig));
        $response = $controller($mockRequest);
        $this->assertEquals(\Symfony\Component\HttpFoundation\Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals(1, $response->getHeaderLine('X-Page-Count'));
        $this->assertEquals('X-Page-Count', $response->getHeaderLine('Access-Control-Expose-Headers'));
    }
}
