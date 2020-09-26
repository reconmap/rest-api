<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\TaskRepository;

class CreateTaskController extends Controller
{

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $projectId = (int)$args['id'];
        $task = $this->getJsonBodyDecoded($request);

        $repository = new TaskRepository($this->db);
        $result = $repository->insert($projectId, $task->parser, $task->name, $task->description);

        return ['success' => $result];
    }
}
