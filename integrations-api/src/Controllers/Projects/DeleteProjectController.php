<?php declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use OpenApi\Attributes as OpenApi;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Http\Docs\Default204NoContentResponse;
use Reconmap\Http\Docs\Default403UnauthorisedResponse;
use Reconmap\Http\Docs\InPathIdParameter;
use Reconmap\Models\AuditActions\AuditActions;
use Reconmap\Repositories\ProjectRepository;
use Reconmap\Services\AuditLogService;

#[OpenApi\Delete(path: "/projects/{projectId}", description: "Deletes project with the given id", security: ["bearerAuth"], tags: ["Projects"], parameters: [new InPathIdParameter("projectId")])]
#[Default204NoContentResponse]
#[Default403UnauthorisedResponse]
class DeleteProjectController extends Controller
{
    public function __construct(private readonly ProjectRepository $projectRepository,
                                private readonly AuditLogService   $auditLogService)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $projectId = (int)$args['projectId'];

        $success = $this->projectRepository->deleteById($projectId);
        if ($success) {
            $userId = $request->getAttribute('userId');
            $this->auditAction($userId, $projectId);
        }

        return $success ? $this->createNoContentResponse() : $this->createInternalServerErrorResponse();
    }

    private function auditAction(int $loggedInUserId, int $projectId): void
    {
        $this->auditLogService->insert($loggedInUserId, AuditActions::DELETED, 'Project', ['id' => $projectId]);
    }
}
