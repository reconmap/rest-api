<?php declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Database\NullColumnReplacer;
use Reconmap\Models\AuditLogAction;
use Reconmap\Repositories\TaskRepository;
use Reconmap\Services\ActivityPublisherService;

class UpdateTaskController extends Controller
{
    public function __construct(private TaskRepository $repository,
                                private ActivityPublisherService $activityPublisherService)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $taskId = (int)$args['taskId'];

        $requestBody = $this->getJsonBodyDecodedAsArray($request);
        $newColumnValues = array_filter(
            $requestBody,
            fn(string $key) => in_array($key, array_keys(TaskRepository::UPDATABLE_COLUMNS_TYPES)),
            ARRAY_FILTER_USE_KEY
        );

        $success = false;
        if (!empty($newColumnValues)) {
            NullColumnReplacer::replaceEmptyWithNulls(['due_date'], $newColumnValues);

            $success = $this->repository->updateById($taskId, $newColumnValues);

            $loggedInUserId = $request->getAttribute('userId');
            $this->auditAction($loggedInUserId, $taskId);
        }

        return ['success' => $success];
    }

    private function auditAction(int $loggedInUserId, int $taskId): void
    {
        $this->activityPublisherService->publish($loggedInUserId, AuditLogAction::TASK_MODIFIED, ['taskId' => $taskId]);
    }
}
