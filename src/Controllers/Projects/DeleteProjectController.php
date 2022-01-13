<?php declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditActions\AuditLogAction;
use Reconmap\Repositories\ProjectRepository;
use Reconmap\Services\AuditLogService;

class DeleteProjectController extends Controller
{
    public function __construct(private ProjectRepository $projectRepository,
                                private AuditLogService   $auditLogService)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $projectId = (int)$args['projectId'];

        $success = $this->projectRepository->deleteById($projectId);
        if ($success) {
            $userId = $request->getAttribute('userId');
            $this->auditAction($userId, $projectId);
        }

        return ['success' => $success];
    }

    private function auditAction(int $loggedInUserId, int $projectId): void
    {
        $this->auditLogService->insert($loggedInUserId, AuditLogAction::PROJECT_DELETED, ['type' => 'project', 'id' => $projectId]);
    }
}
