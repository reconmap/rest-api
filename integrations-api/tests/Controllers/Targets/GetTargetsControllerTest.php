<?php declare(strict_types=1);

namespace Reconmap\Controllers\Targets;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Repositories\SearchCriterias\TargetSearchCriteria;
use Reconmap\Repositories\TargetRepository;

class GetTargetsControllerTest extends TestCase
{
    public function testHappyPath()
    {
        $mockParams = ['projectId' => 5, 'page' => 1];

        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->exactly(2))
            ->method('getQueryParams')
            ->willReturn($mockParams);

        $expectedTarget = ['id' => 5];

        $mockTargetRepository = $this->createMock(TargetRepository::class);
        $mockTargetRepository->expects($this->once())
            ->method('search')
            ->willReturn($expectedTarget);
        $mockTargetRepository->expects($this->once())
            ->method('countSearch')
            ->willReturn(4);

        $mockTargetSearchCriteria = $this->createMock(TargetSearchCriteria::class);
        $mockTargetSearchCriteria->expects($this->once())
            ->method('addProjectCriterion')
            ->with(5);

        $controller = new GetTargetsController($mockTargetRepository, $mockTargetSearchCriteria);
        $response = $controller($mockRequest);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, $response->getHeaderLine('X-Page-Count'));
        $this->assertEquals('X-Page-Count', $response->getHeaderLine('Access-Control-Expose-Headers'));
    }
}
