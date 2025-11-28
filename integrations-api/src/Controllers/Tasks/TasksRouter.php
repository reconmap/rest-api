<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use League\Route\RouteCollectionInterface;

class TasksRouter
{
    public function mapRoutes(RouteCollectionInterface $router): void
    {
        $router->map('POST', '/tasks', CreateTaskController::class);
        $router->map('GET', '/tasks', GetTasksController::class);
        $router->map('PATCH', '/tasks', BulkUpdateTasksController::class);
        $router->map('GET', '/tasks/{taskId:number}', GetTaskController::class);
        $router->map('POST', '/tasks/{taskId:number}/clone', CloneTaskController::class);
        $router->map('PATCH', '/tasks/{taskId:number}', UpdateTaskController::class);
    }
}
