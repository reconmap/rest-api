<?php declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Models\AuditActions\AuditLogAction;
use Reconmap\Repositories\ProjectRepository;
use Reconmap\Services\AuditLogService;

class DeleteProjectControllerTest extends TestCase
{
    public function testHappyPath()
    {
        $fakeProjectId = 4;

        $mockProjectRepository = $this->createMock(ProjectRepository::class);
        $mockProjectRepository->expects($this->once())
            ->method('deleteById')
            ->with($fakeProjectId)
            ->willReturn(true);

        $mockAuditLogService = $this->createMock(AuditLogService::class);
        $mockAuditLogService->expects($this->once())
            ->method('insert')
            ->with(9, AuditLogAction::PROJECT_DELETED, ['type' => 'project', 'id' => $fakeProjectId]);

        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->once())
            ->method('getAttribute')
            ->with('userId')
            ->willReturn(9);

        $args = ['projectId' => $fakeProjectId];

        $controller = new DeleteProjectController($mockProjectRepository, $mockAuditLogService);
        $response = $controller($mockRequest, $args);

        $this->assertTrue($response['success']);
    }
}
