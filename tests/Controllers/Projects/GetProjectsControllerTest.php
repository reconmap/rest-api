<?php declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Repositories\ProjectRepository;
use Reconmap\Repositories\QueryBuilders\SearchCriteria;

class GetProjectsControllerTest extends TestCase
{
    public function testHappyPath()
    {
        $mockProjects = [['title' => 'foo']];

        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->exactly(2))
            ->method('getQueryParams')
            ->willReturn(['status' => 'archived']);
        $mockRequest->expects($this->exactly(2))
            ->method('getAttribute')
            ->withConsecutive(['userId'], ['role'])
            ->willReturnOnConsecutiveCalls(9, 'administrator');

        $searchCriteria = new SearchCriteria();
        $searchCriteria->addCriterion('p.archived = ?', [true]);
        $searchCriteria->addCriterion('p.is_template = 0');

        $mockRepository = $this->createMock(ProjectRepository::class);
        $mockRepository->expects($this->once())
            ->method('search')
            ->with($searchCriteria)
            ->willReturn($mockProjects);

        $controller = new GetProjectsController($mockRepository);
        $response = $controller($mockRequest);

        $this->assertEquals($mockProjects, $response);
    }
}
