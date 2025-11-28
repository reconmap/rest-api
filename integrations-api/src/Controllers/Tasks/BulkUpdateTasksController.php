<?php declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditActions\AuditActions;
use Reconmap\Repositories\TaskRepository;
use Reconmap\Services\AuditLogService;

class BulkUpdateTasksController extends Controller
{
    public function __construct(private readonly TaskRepository  $taskRepository,
                                private readonly AuditLogService $auditLogService)
    {
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        $operation = $request->getHeaderLine('Bulk-Operation');
        $requestData = $this->getJsonBodyDecodedAsArray($request);

        $this->logger->debug("Bulk-Operation: $operation", $requestData);

        $loggedInUserId = $request->getAttribute('userId');

        $numSuccesses = 0;

        switch ($operation) {
            case 'UPDATE':
                $taskIds = $requestData['taskIds'];
                // @todo Do in one SQL update statement
                foreach ($taskIds as $taskId) {
                    $numSuccesses = $this->taskRepository->updateById($taskId, ['status' => $requestData['newStatus']]);
                }
                $this->auditLogService->insert($loggedInUserId, AuditActions::UPDATED, 'Task', ['ids' => $taskIds]);
                break;
            case 'DELETE':
                $taskIds = $requestData;
                $numSuccesses = $this->taskRepository->deleteByIds($taskIds);
                $this->auditLogService->insert($loggedInUserId, AuditActions::DELETED, 'Task', ['ids' => $taskIds]);
                break;
        }

        return ['numSuccesses' => $numSuccesses];
    }
}
