<?php

declare(strict_types=1);

namespace Reconmap;

use League\Route\RouteCollectionInterface;
use Reconmap\Controllers\Projects\AddProjectUserController;
use Reconmap\Controllers\Projects\DeleteProjectController;
use Reconmap\Controllers\Projects\GetProjectController;
use Reconmap\Controllers\Projects\GetProjectsController;
use Reconmap\Controllers\Projects\GetProjectTargetsController;
use Reconmap\Controllers\Projects\GetProjectTasksController;
use Reconmap\Controllers\Projects\CloneProjectController;
use Reconmap\Controllers\Projects\DeleteProjectUserController;
use Reconmap\Controllers\Projects\GetProjectUsersController;
use Reconmap\Controllers\Projects\GetProjectVulnerabilitiesController;
use Reconmap\Controllers\Tasks\CreateTaskController;
use Reconmap\Controllers\Projects\ImportTemplateController;

class ProjectsRouter
{

    public function mapRoutes(RouteCollectionInterface $router): void
    {
        $router->map('GET', '/projects', GetProjectsController::class);
        $router->map('GET', '/projects/{id:number}', GetProjectController::class);
        $router->map('POST', '/projects', ImportTemplateController::class);
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
