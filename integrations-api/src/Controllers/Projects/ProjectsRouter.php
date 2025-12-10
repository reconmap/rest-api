<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use League\Route\RouteCollectionInterface;

class ProjectsRouter
{
    public function mapRoutes(RouteCollectionInterface $router): void
    {
        $router->map('GET', '/projects', GetProjectsController::class);
        $router->map('POST', '/projects/{projectId:number}/clone', CloneProjectController::class);
    }
}
