<?php declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditActions\AuditActions;
use Reconmap\Repositories\ProjectRepository;
use Reconmap\Services\AuditLogService;

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
