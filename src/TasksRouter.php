<?php

declare(strict_types=1);

namespace Reconmap;

use League\Route\RouteCollectionInterface;
use Reconmap\Controllers\Tasks\GetTaskResultsController;
use Reconmap\Controllers\Tasks\GetTaskController;
use Reconmap\Controllers\Tasks\GetTasksController;
use Reconmap\Controllers\Tasks\UploadTaskResultController;
use Reconmap\Controllers\Tasks\DeleteTaskController;
use Reconmap\Controllers\Tasks\UpdateTaskController;

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
