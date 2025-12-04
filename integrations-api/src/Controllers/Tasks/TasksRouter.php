<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use League\Route\RouteCollectionInterface;

class TasksRouter
{
    public function mapRoutes(RouteCollectionInterface $router): void
    {
        $router->map('PATCH', '/tasks', BulkUpdateTasksController::class);
    }
}
