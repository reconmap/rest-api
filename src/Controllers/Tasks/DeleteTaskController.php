<?php declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditActions\AuditActions;
use Reconmap\Repositories\TaskRepository;
use Reconmap\Services\ActivityPublisherService;

class DeleteTaskController extends Controller
{
    public function __construct(private readonly TaskRepository           $repository,
                                private readonly ActivityPublisherService $activityPublisherService)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $taskId = (int)$args['taskId'];

        $success = $this->repository->deleteById($taskId);

        $userId = $request->getAttribute('userId');
        $this->auditAction($userId, $taskId);

        return $success ? $this->createNoContentResponse() : $this->createInternalServerErrorResponse();
    }

    private function auditAction(int $loggedInUserId, int $taskId): void
    {
        $this->activityPublisherService->publish($loggedInUserId, AuditActions::DELETED, 'Task', ['id' => $taskId]);
    }
}
