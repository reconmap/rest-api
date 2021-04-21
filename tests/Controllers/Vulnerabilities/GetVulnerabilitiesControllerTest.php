<?php declare(strict_types=1);

namespace Reconmap\Controllers\Vulnerabilities;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Repositories\VulnerabilityRepository;
use Reconmap\Repositories\VulnerabilityStatsRepository;

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

        $mockStatsRepository = $this->createMock(VulnerabilityStatsRepository::class);
        $mockStatsRepository->expects($this->once())
            ->method('countAll')
            ->willReturn(32);

        $controller = new GetVulnerabilitiesController($mockRepository, $mockStatsRepository);
        $response = $controller($mockRequest);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(2, $response->getHeaderLine('X-Page-Count'));
        $this->assertEquals('X-Page-Count', $response->getHeaderLine('Access-Control-Expose-Headers'));
    }
}
