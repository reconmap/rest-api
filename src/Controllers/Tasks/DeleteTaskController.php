<?php declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditActions\AuditActions;
use Reconmap\Models\AuditActions\AuditLogAction;
use Reconmap\Repositories\TaskRepository;
use Reconmap\Services\ActivityPublisherService;

class DeleteTaskController extends Controller
{
    public function __construct(private TaskRepository           $repository,
                                private ActivityPublisherService $activityPublisherService)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $taskId = (int)$args['taskId'];

        $success = $this->repository->deleteById($taskId);

        $userId = $request->getAttribute('userId');
        $this->auditAction($userId, $taskId);

        return ['success' => $success];
    }

    private function auditAction(int $loggedInUserId, int $taskId): void
    {
        $this->activityPublisherService->publish($loggedInUserId, AuditActions::DELETED, 'Task', ['id' => $taskId]);
    }
}
