<?php declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\TaskRepository;

class GetTaskController extends Controller
{
    public function __construct(private TaskRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $taskId = (int)$args['taskId'];

        return $this->repository->findById($taskId);
    }
}
