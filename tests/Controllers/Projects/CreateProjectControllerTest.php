<?php declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Models\AuditActions\AuditActions;
use Reconmap\Models\Project;
use Reconmap\Repositories\ProjectRepository;
use Reconmap\Repositories\ProjectUserRepository;
use Reconmap\Services\AuditLogService;
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

        $mockProjectUserRepository = $this->createMock(ProjectUserRepository::class);
        $mockAuditLogService = $this->createMock(AuditLogService::class);
        $mockAuditLogService->expects($this->once())
            ->method('insert')
            ->with(9, AuditActions::CREATED, 'Project', ['id' => 1]);

        $controller = new CreateProjectController($mockProjectRepository, $mockProjectUserRepository, $mockAuditLogService);
        $response = $controller($mockRequest);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
    }
}
