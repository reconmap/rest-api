<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use League\Route\RouteCollectionInterface;
use Reconmap\Controllers\Vault\ReadProjectVaultController;

class ProjectsRouter
{
    public function mapRoutes(RouteCollectionInterface $router): void
    {
        $router->map('GET', '/projects', GetProjectsController::class);
        $router->map('GET', '/projects/{projectId:number}', GetProjectController::class);
        $router->map('GET', '/projects/{projectId:number}/vault', ReadProjectVaultController::class);
        $router->map('PUT', '/projects/{projectId:number}', UpdateProjectController::class);
        $router->map('PATCH', '/projects/{projectId:number}', UpdateProjectController::class);
        $router->map('POST', '/projects/{projectId:number}/clone', CloneProjectController::class);
        $router->map('GET', '/projects/{projectId:number}/tasks', GetProjectTasksController::class);
        $router->map('GET', '/projects/{projectId:number}/users', GetProjectUsersController::class);
        $router->map('POST', '/projects/{projectId:number}/users', AddProjectUserController::class);
    }
}
