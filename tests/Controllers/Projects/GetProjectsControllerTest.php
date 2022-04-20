<?php declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Repositories\ProjectRepository;
use Reconmap\Repositories\SearchCriterias\ProjectSearchCriteria;

class GetProjectsControllerTest extends TestCase
{
    public function testGetRegularProjects()
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

        $searchCriteria = new ProjectSearchCriteria();
        $searchCriteria->addCriterion('p.archived = ?', [true]);
        $searchCriteria->addCriterion('p.is_template = ?', [false]);

        $mockRepository = $this->createMock(ProjectRepository::class);
        $mockRepository->expects($this->once())
            ->method('search')
            ->with($searchCriteria)
            ->willReturn($mockProjects);

        $controller = new GetProjectsController($mockRepository, $searchCriteria);
        $response = $controller($mockRequest);

        $this->assertEquals(json_encode($mockProjects), (string)$response->getBody());
    }

    public function testGetProjectTemplates()
    {
        $mockProjects = [['title' => 'foo']];

        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->exactly(2))
            ->method('getQueryParams')
            ->willReturn(['status' => 'archived', 'isTemplate' => true]);
        $mockRequest->expects($this->exactly(2))
            ->method('getAttribute')
            ->withConsecutive(['userId'], ['role'])
            ->willReturnOnConsecutiveCalls(9, 'administrator');

        $searchCriteria = new ProjectSearchCriteria();
        $searchCriteria->addCriterion('p.archived = ?', [true]);
        $searchCriteria->addCriterion('p.is_template = ?', [true]);

        $mockRepository = $this->createMock(ProjectRepository::class);
        $mockRepository->expects($this->once())
            ->method('search')
            ->with($searchCriteria)
            ->willReturn($mockProjects);

        $controller = new GetProjectsController($mockRepository, $searchCriteria);
        $response = $controller($mockRequest);

        $this->assertEquals(json_encode($mockProjects), (string)$response->getBody());
    }
}
