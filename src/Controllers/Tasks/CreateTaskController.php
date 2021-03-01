<?php declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\Task;
use Reconmap\Repositories\TaskRepository;

class CreateTaskController extends Controller
{
    public function __construct(private TaskRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        /** @var Task $task */
        $task = $this->getJsonBodyDecoded($request);
        $task->creator_uid = $request->getAttribute('userId');

        $result = $this->repository->insert($task);

        return ['success' => $result];
    }
}
