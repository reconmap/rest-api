<?php declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use Fig\Http\Message\StatusCodeInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Models\Project;
use Reconmap\Repositories\ProjectRepository;

class CreateProjectControllerTest extends TestCase
{
    public function testHappyPath()
    {
        $expectedProject = new Project();
        $expectedProject->name = 'exciting new project';
        $expectedProject->creator_uid = 9;
        $expectedProject->is_template = true;

        $mockProjectRepository = $this->createMock(ProjectRepository::class);
        $mockProjectRepository->expects($this->once())
            ->method('insert')
            ->with($expectedProject)
            ->willReturn(1);

        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->once())
            ->method('getAttribute')
            ->with('userId')
            ->willReturn(9);
        $mockRequest->expects($this->once())
            ->method('getBody')
            ->willReturn('{"name": "exciting new project", "is_template": true}');

        $controller = new CreateProjectController($mockProjectRepository);
        $response = $controller($mockRequest);

        $this->assertEquals(StatusCodeInterface::STATUS_CREATED, $response->getStatusCode());
    }
}
