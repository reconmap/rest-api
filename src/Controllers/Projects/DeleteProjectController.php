<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditLogAction;
use Reconmap\Repositories\ProjectRepository;
use Reconmap\Services\AuditLogService;

class DeleteProjectController extends Controller
{

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $projectId = (int)$args['id'];

        $repository = new ProjectRepository($this->db);
        $success = $repository->deleteById($projectId);

        $userId = $request->getAttribute('userId');
        $this->auditAction($userId, $projectId);

        return ['success' => $success];
    }

    private function auditAction(int $loggedInUserId, int $projectId): void
    {
        $auditLogService = new AuditLogService($this->db);
        $auditLogService->insert($loggedInUserId, AuditLogAction::PROJECT_DELETED, ['type' => 'project', 'id' => $projectId]);
    }
}
