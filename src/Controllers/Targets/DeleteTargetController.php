<?php declare(strict_types=1);

namespace Reconmap\Controllers\Targets;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditLogAction;
use Reconmap\Repositories\TargetRepository;
use Reconmap\Services\AuditLogService;

class DeleteTargetController extends Controller
{
    public function __construct(private TargetRepository $repository,
                                private AuditLogService $auditLogService)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $targetId = (int)$args['targetId'];

        $success = $this->repository->deleteById($targetId);

        $userId = $request->getAttribute('userId');
        $this->auditAction($userId, $targetId);

        return ['success' => $success];
    }

    private function auditAction(int $loggedInUserId, int $targetId): void
    {
        $this->auditLogService->insert($loggedInUserId, AuditLogAction::TARGET_DELETED, ['type' => 'target', 'id' => $targetId]);
    }
}
