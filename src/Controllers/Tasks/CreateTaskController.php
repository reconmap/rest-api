<?php declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\Task;
use Reconmap\Repositories\TaskRepository;

class CreateTaskController extends Controller
{
    public function __construct(private readonly TaskRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        /** @var Task $task */
        $task = $this->getJsonBodyDecodedAsClass($request, new Task());
        $task->creator_uid = $request->getAttribute('userId');

        $taskId = $this->repository->insert($task);

        return $this->createStatusCreatedResponse(['taskId' => $taskId]);
    }
}
