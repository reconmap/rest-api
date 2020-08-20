<?php

declare(strict_types=1);

namespace Reconmap;

use Laminas\Diactoros\ResponseFactory;
use League\Container\Container;
use League\Route\RouteGroup;
use League\Route\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\AuditLog\GetAuditLogStatsController;
use Reconmap\Controllers\AuditLog\ExportAuditLogController;
use Reconmap\Controllers\AuditLog\GetAuditLogController;
use Reconmap\Controllers\Projects\DeleteProjectController;
use Reconmap\Controllers\Projects\GetProjectController;
use Reconmap\Controllers\Projects\GetProjectsController;
use Reconmap\Controllers\Projects\GetProjectTargetsController;
use Reconmap\Controllers\Projects\GetProjectTasksController;
use Reconmap\Controllers\GetVulnerabilityController;
use Reconmap\Controllers\IndexController;
use Reconmap\Controllers\Projects\CloneProjectController;
use Reconmap\Controllers\Projects\GenerateReport;
use Reconmap\Controllers\Projects\GetProjectVulnerabilitiesController;
use Reconmap\Controllers\Tasks\GetTaskResultsController;
use Reconmap\Controllers\Tasks\GetTaskController;
use Reconmap\Controllers\Tasks\GetTasksController;
use Reconmap\Controllers\Tasks\UploadTaskResultController;
use Reconmap\Controllers\Users\CreateUserController;
use Reconmap\Controllers\Users\DeleteUserController;
use Reconmap\Controllers\Users\GetUserController;
use Reconmap\Controllers\Users\GetUsersController;
use Reconmap\Controllers\Users\UsersLoginController;

class ApiRouter extends Router
{


    public function mapRoutes(Container $container): void
    {
        $authMiddleware = new AuthMiddleware;

        $responseFactory = new ResponseFactory;
        $strategy = new \League\Route\Strategy\JsonStrategy($responseFactory);
        $strategy->setContainer($container);

        $this->setStrategy($strategy);

        // OPTIONS to support CORS
        $this->map('OPTIONS', '/{any:.*}', function (ServerRequestInterface $request): ResponseInterface {
            $response = (new \GuzzleHttp\Psr7\Response)->withStatus(200);
            return $response
                ->withHeader('Access-Control-Allow-Methods', 'GET,POST,PUT,DELETE')
                ->withHeader('Access-Control-Allow-Headers', 'Authorization')
                ->withHeader('Access-Control-Allow-Origin', '*');
        });

        $this->map('GET', '/', IndexController::class);
        $this->map('POST', '/users/login', UsersLoginController::class);
        $this->group('', function (RouteGroup $router): void {
            $router->map('GET', '/users', GetUsersController::class);
            $router->map('POST', '/users', CreateUserController::class);
            $router->map('GET', '/users/{id:number}', GetUserController::class);
            $router->map('DELETE', '/users/{id:number}', DeleteUserController::class);
            $router->map('GET', '/vulnerabilities', GetVulnerabilityController::class);
            $router->map('GET', '/auditlog', GetAuditLogController::class);
            $router->map('GET', '/auditlog/export', ExportAuditLogController::class);
            $router->map('GET', '/auditlog/stats',GetAuditLogStatsController::class);
            $router->map('POST', '/tasks/results', UploadTaskResultController::class);
            $router->map('GET', '/tasks', GetTasksController::class);
            $router->map('GET', '/tasks/{id:number}', GetTaskController::class);
            $router->map('GET', '/tasks/{id:number}/results', GetTaskResultsController::class);
            $router->map('GET', '/projects', GetProjectsController::class);
            $router->map('GET', '/projects/{id:number}', GetProjectController::class);
            $router->map('GET', '/projects/{id:number}/report', GenerateReport::class);
            $router->map('POST', '/projects/{id:number}/clone', CloneProjectController::class);
            $router->map('GET', '/projects/{id:number}/tasks', GetProjectTasksController::class);
            $router->map('GET', '/projects/{id:number}/targets', GetProjectTargetsController::class);
            $router->map('GET', '/projects/{id:number}/vulnerabilities', GetProjectVulnerabilitiesController::class);
            $router->map('DELETE', '/projects/{id:number}', DeleteProjectController::class);
        })->middleware($authMiddleware);
    }
}
