<?php declare(strict_types=1);

namespace Reconmap\Controllers\Vulnerabilities;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Repositories\VulnerabilityRepository;

class GetVulnerabilitiesControllerTest extends TestCase
{
    public function testNoFilters()
    {
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->once())
            ->method('getQueryParams')
            ->willReturn(['page' => 5]);

        $mockRepository = $this->createMock(VulnerabilityRepository::class);
        $mockRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([]);

        $controller = new GetVulnerabilitiesController($mockRepository);
        $response = $controller($mockRequest);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, $response->getHeaderLine('X-Page-Count'));
        $this->assertEquals('X-Page-Count', $response->getHeaderLine('Access-Control-Expose-Headers'));
    }
}
