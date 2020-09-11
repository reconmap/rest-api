<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use League\Route\RouteCollectionInterface;

class TasksRouter
{

    public function mapRoutes(RouteCollectionInterface $router): void
    {
        $router->map('POST', '/tasks/results', UploadTaskResultController::class);
        $router->map('GET', '/tasks', GetTasksController::class);
        $router->map('GET', '/tasks/{id:number}', GetTaskController::class);
        $router->map('PATCH', '/tasks/{id:number}', UpdateTaskController::class);
        $router->map('DELETE', '/tasks/{id:number}', DeleteTaskController::class);
        $router->map('GET', '/tasks/{id:number}/results', GetTaskResultsController::class);
    }
}
