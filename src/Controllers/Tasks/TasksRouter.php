<?php declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use League\Route\RouteCollectionInterface;

class TasksRouter
{
    public function mapRoutes(RouteCollectionInterface $router): void
    {
        $router->map('POST', '/tasks', CreateTaskController::class);
        $router->map('GET', '/tasks', GetTasksController::class);
        $router->map('GET', '/tasks/{taskId:number}', GetTaskController::class);
        $router->map('PATCH', '/tasks/{taskId:number}', UpdateTaskController::class);
        $router->map('DELETE', '/tasks/{taskId:number}', DeleteTaskController::class);
    }
}
