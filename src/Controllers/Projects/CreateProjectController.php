<?php declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use OpenApi\Attributes as OpenApi;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Http\Docs\Default201CreatedResponse;
use Reconmap\Http\Docs\Default403UnauthorisedResponse;
use Reconmap\Models\AuditActions\AuditActions;
use Reconmap\Models\Project;
use Reconmap\Repositories\ProjectRepository;
use Reconmap\Repositories\ProjectUserRepository;
use Reconmap\Services\AuditLogService;

#[OpenApi\Post(
    path: "/projects",
    description: "Creates a new project",
    security: ["bearerAuth"],
    tags: ["Projects"],
)]
#[Default201CreatedResponse]
#[Default403UnauthorisedResponse]
class CreateProjectController extends Controller
{
    public function __construct(private readonly ProjectRepository $projectRepository, private readonly ProjectUserRepository $projectUserRepository, private readonly AuditLogService $auditLogService)
    {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $project = $this->getJsonBodyDecodedAsClass($request, new Project());
        $loggedUserId = $request->getAttribute('userId');
        $project->creator_uid = $loggedUserId;

        $project->id = $this->projectRepository->insert($project);

        $this->projectUserRepository->create($project->id, $loggedUserId);

        $this->auditAction($loggedUserId, $project->id);

        return $this->createStatusCreatedResponse($project);
    }

    private function auditAction(int $loggedInUserId, int $projectId): void
    {
        $this->auditLogService->insert($loggedInUserId, AuditActions::CREATED, 'Project', ['id' => $projectId]);
    }
}
