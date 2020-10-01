<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\TaskRepository;

class UpdateTaskController extends Controller
{

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $id = (int)$args['id'];

        $requestBody = json_decode((string)$request->getBody(), true);
        $column = array_keys($requestBody)[0];
        $value = array_values($requestBody)[0];

        $success = false;
        if (in_array($column, ['completed', 'assignee_uid'])) {
            $repository = new TaskRepository($this->db);
            $success = $repository->updateById($id, $column, $value);
        }

        return ['success' => $success];
    }
}
