<?php declare(strict_types=1);

namespace Reconmap\Controllers\Vulnerabilities;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Repositories\SearchCriterias\VulnerabilitySearchCriteria;
use Reconmap\Repositories\VulnerabilityRepository;
use Reconmap\Repositories\VulnerabilityStatsRepository;

class GetVulnerabilitiesControllerTest extends TestCase
{
    public function testFindingVulnerabilitiesByKeywords()
    {
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->atLeastOnce())
            ->method('getQueryParams')
            ->willReturn(['keywords' => 'foo', 'page' => 5]);

        $mockRepository = $this->createMock(VulnerabilityRepository::class);
        $mockRepository->expects($this->once())
            ->method('search')
            ->willReturn([]);

        $mockSearchCriteria = $this->createMock(VulnerabilitySearchCriteria::class);
        $mockSearchCriteria->expects($this->once())
            ->method('addKeywordsCriterion')
            ->with('foo');

        $mockStatsRepository = $this->createMock(VulnerabilityStatsRepository::class);
        $mockStatsRepository->expects($this->once())
            ->method('countAll')
            ->willReturn(32);

        $controller = new GetVulnerabilitiesController($mockRepository, $mockSearchCriteria, $mockStatsRepository);
        $response = $controller($mockRequest);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(2, $response->getHeaderLine('X-Page-Count'));
        $this->assertEquals('X-Page-Count', $response->getHeaderLine('Access-Control-Expose-Headers'));
    }
}
