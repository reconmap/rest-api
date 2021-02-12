<?php declare(strict_types=1);

namespace Reconmap\Controllers\AuditLog;

use GuzzleHttp\Psr7\ServerRequest;
use Reconmap\ControllerTestCase;
use Reconmap\Repositories\AuditLogRepository;

class GetAuditLogControllerTest extends ControllerTestCase
{
    public function testResponse()
    {
        $mockRepository = $this->createPartialMock(AuditLogRepository::class, ['findAll', 'countAll']);
        $mockRepository->expects($this->once())
            ->method('findAll')
            ->with(1);
        $request = (new ServerRequest('get', '/auditlog'))
            ->withQueryParams([
                'page' => 1
            ]);

        /**
         * @var $controller GetAuditLogController
         */
        $controller = $this->injectController(new GetAuditLogController($mockRepository));
        $response = $controller($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, $response->getHeaderLine('X-Page-Count'));
        $this->assertEquals('X-Page-Count', $response->getHeaderLine('Access-Control-Expose-Headers'));
    }
}
