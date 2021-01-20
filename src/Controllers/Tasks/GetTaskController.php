<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\TaskRepository;

class GetTaskController extends Controller
{

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $taskId = (int)$args['taskId'];

        $repository = new TaskRepository($this->db);
        return $repository->findById($taskId);
    }
}
