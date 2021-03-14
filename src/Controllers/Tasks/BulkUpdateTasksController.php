<?php declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditLogAction;
use Reconmap\Repositories\TaskRepository;
use Reconmap\Services\AuditLogService;

class BulkUpdateTasksController extends Controller
{
    public function __construct(private TaskRepository $taskRepository,
                                private AuditLogService $auditLogService)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $operation = $request->getHeaderLine('Bulk-Operation');
        $tasksIds = $this->getJsonBodyDecodedAsArray($request);

        $this->logger->debug("Bulk-Operation: $operation", $tasksIds);

        $numSuccesses = 0;

        if ('DELETE' === $operation) {
            $numSuccesses = $this->taskRepository->deleteByIds($tasksIds);
        }

        $loggedInUserId = $request->getAttribute('userId');

        $this->auditAction($loggedInUserId, $tasksIds);

        return ['numSuccesses' => $numSuccesses];
    }

    private function auditAction(int $loggedInUserId, array $userIds): void
    {
        $this->auditLogService->insert($loggedInUserId, AuditLogAction::USER_DELETED, ['type' => 'tasks', 'ids' => $userIds]);
    }
}
