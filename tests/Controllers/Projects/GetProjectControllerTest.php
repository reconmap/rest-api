<?php declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use League\Route\Http\Exception\NotFoundException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Repositories\ProjectRepository;

class GetProjectControllerTest extends TestCase
{
    public function testRetrievingProject()
    {
        $mockProject = ['title' => 'foo'];

        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->exactly(2))
            ->method('getAttribute')
            ->withConsecutive(['userId'], ['role'])
            ->willReturnOnConsecutiveCalls(9, 'administrator');

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

    public function testRetrievingMissingProject()
    {
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->exactly(2))
            ->method('getAttribute')
            ->withConsecutive(['userId'], ['role'])
            ->willReturnOnConsecutiveCalls(9, 'administrator');

        $mockRepository = $this->createMock(ProjectRepository::class);
        $mockRepository->expects($this->once())
            ->method('findById')
            ->with(568)
            ->willReturn(null);

        $args = ['projectId' => 568];

        $this->expectException(NotFoundException::class);

        $controller = new GetProjectController($mockRepository);
        $controller($mockRequest, $args);
    }
}
