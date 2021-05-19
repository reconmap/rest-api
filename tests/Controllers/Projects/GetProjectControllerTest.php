<?php declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Repositories\ProjectRepository;

class GetProjectControllerTest extends TestCase
{
    public function testHappyPath()
    {
        $mockProject = ['title' => 'foo'];

        $mockRequest = $this->createMock(ServerRequestInterface::class);

        $mockRepository = $this->createMock(ProjectRepository::class);
        $mockRepository->expects($this->once())
            ->method('findById')
            ->with(568)
            ->willReturn($mockProject);

        $args = ['projectId' => 568];

        $controller = new GetProjectController($mockRepository);
        $response = $controller($mockRequest, $args);

        $this->assertEquals($mockProject, $response);
    }
}
