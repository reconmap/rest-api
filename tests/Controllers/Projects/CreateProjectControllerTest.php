<?php declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Models\Project;
use Reconmap\Repositories\ProjectRepository;
use Symfony\Component\HttpFoundation\Response;

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
            ->willReturn(Utils::streamFor('{"name": "exciting new project", "is_template": true}'));

        $controller = new CreateProjectController($mockProjectRepository);
        $response = $controller($mockRequest);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
    }
}
