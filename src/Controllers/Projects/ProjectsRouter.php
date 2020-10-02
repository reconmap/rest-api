<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use League\Route\RouteCollectionInterface;
use Reconmap\Controllers\Tasks\CreateTaskController;

class ProjectsRouter
{

    public function mapRoutes(RouteCollectionInterface $router): void
    {
        $router->map('POST', '/templates', ImportTemplateController::class);
        $router->map('GET', '/projects', GetProjectsController::class);
        $router->map('GET', '/projects/{id:number}', GetProjectController::class);
        $router->map('PATCH', '/projects/{projectId:number}', UpdateProjectController::class);
        $router->map('POST', '/projects', CreateProjectController::class);
        $router->map('POST', '/projects/{id:number}/clone', CloneProjectController::class);
        $router->map('GET', '/projects/{id:number}/tasks', GetProjectTasksController::class);
        $router->map('POST', '/projects/{id:number}/tasks', CreateTaskController::class);
        $router->map('GET', '/projects/{id:number}/targets', GetProjectTargetsController::class);
        $router->map('GET', '/projects/{id:number}/users', GetProjectUsersController::class);
        $router->map('POST', '/projects/{id:number}/users', AddProjectUserController::class);
        $router->map('DELETE', '/projects/{projectId:number}/users/{membershipId:number}', DeleteProjectUserController::class);
        $router->map('GET', '/projects/{id:number}/vulnerabilities', GetProjectVulnerabilitiesController::class);
        $router->map('DELETE', '/projects/{id:number}', DeleteProjectController::class);
    }
}
