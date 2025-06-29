<?php declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use OpenApi\Attributes as OpenApi;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Http\Docs\Default204NoContentResponse;
use Reconmap\Http\Docs\Default403UnauthorisedResponse;
use Reconmap\Http\Docs\InPathIdParameter;
use Reconmap\Models\AuditActions\AuditActions;
use Reconmap\Repositories\TaskRepository;
use Reconmap\Services\ActivityPublisherService;

#[OpenApi\Delete(path: "/tasks/{taskId}", description: "Deletes task with the given id", security: ["bearerAuth"], tags: ["Tasks"], parameters: [new InPathIdParameter("taskId")])]
#[Default204NoContentResponse]
#[Default403UnauthorisedResponse]
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
