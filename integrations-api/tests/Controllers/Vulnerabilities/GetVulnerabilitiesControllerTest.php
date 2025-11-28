<?php declare(strict_types=1);

namespace Reconmap\Controllers\Vulnerabilities;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\ConsecutiveParamsTrait;
use Reconmap\Repositories\SearchCriterias\VulnerabilitySearchCriteria;
use Reconmap\Repositories\VulnerabilityRepository;

class GetVulnerabilitiesControllerTest extends TestCase
{
    use ConsecutiveParamsTrait;

    public function testFindingVulnerabilitiesByKeywords()
    {
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->exactly(2))
            ->method('getAttribute')
            ->with(...$this->consecutiveParams(['userId'], ['role']))
            ->willReturnOnConsecutiveCalls(9, 'superuser');
        $mockRequest->expects($this->atLeastOnce())
            ->method('getQueryParams')
            ->willReturn(['keywords' => 'foo', 'page' => 5]);

        $mockRepository = $this->createMock(VulnerabilityRepository::class);
        $mockRepository->expects($this->once())
            ->method('search')
            ->willReturn([]);
        $mockRepository->expects($this->once())
            ->method('count')
            ->willReturn(16);

        $mockSearchCriteria = $this->createMock(VulnerabilitySearchCriteria::class);
        $mockSearchCriteria->expects($this->once())
            ->method('addKeywordsCriterion')
            ->with('foo');

        $controller = new GetVulnerabilitiesController($mockRepository, $mockSearchCriteria);
        $response = $controller($mockRequest);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, $response->getHeaderLine('X-Page-Count'));
        $this->assertEquals('X-Total-Count,X-Page-Count', $response->getHeaderLine('Access-Control-Expose-Headers'));
    }

    public function testGetVulnerabilityTemplates()
    {
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->exactly(2))
            ->method('getAttribute')
            ->with(...$this->consecutiveParams(['userId'], ['role']))
            ->willReturnOnConsecutiveCalls(9, 'superuser');
        $mockRequest->expects($this->atLeastOnce())
            ->method('getQueryParams')
            ->willReturn(['keywords' => 'foo', 'page' => 5, 'isTemplate' => true]);

        $mockRepository = $this->createMock(VulnerabilityRepository::class);
        $mockRepository->expects($this->once())
            ->method('search')
            ->willReturn([]);
        $mockRepository->expects($this->once())
            ->method('count')
            ->willReturn(16);

        $mockSearchCriteria = $this->createMock(VulnerabilitySearchCriteria::class);
        $mockSearchCriteria->expects($this->once())
            ->method('addKeywordsCriterion')
            ->with('foo');
        $mockSearchCriteria->expects($this->once())
            ->method('addTemplateCriterion')
            ->with(true);

        $controller = new GetVulnerabilitiesController($mockRepository, $mockSearchCriteria);
        $response = $controller($mockRequest);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, $response->getHeaderLine('X-Page-Count'));
        $this->assertEquals('X-Total-Count,X-Page-Count', $response->getHeaderLine('Access-Control-Expose-Headers'));
    }
}
