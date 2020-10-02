<?php

declare(strict_types=1);

namespace Reconmap;

use Laminas\Diactoros\ResponseFactory;
use League\Container\Container;
use League\Route\RouteGroup;
use League\Route\Router;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\AuditLog\AuditLogRouter;
use Reconmap\Controllers\Clients\ClientsRouter;
use Reconmap\Controllers\Integrations\IntegrationsRouter;
use Reconmap\Controllers\Projects\ProjectsRouter;
use Reconmap\Controllers\Reports\ReportsRouter;
use Reconmap\Controllers\Targets\TargetsRouter;
use Reconmap\Controllers\Tasks\TasksRouter;
use Reconmap\Controllers\Users\UsersLoginController;
use Reconmap\Controllers\Users\UsersRouter;
use Reconmap\Controllers\Vulnerabilities\VulnerabilitiesRouter;

class ApiRouter extends Router
{

    private function setupStrategy(Container $container)
    {
        $responseFactory = new ResponseFactory;
        $strategy = new \League\Route\Strategy\JsonStrategy($responseFactory);
        $strategy->setContainer($container);
        $this->setStrategy($strategy);
    }

    public function mapRoutes(Container $container, Logger $logger): void
    {
        $authMiddleware = new AuthMiddleware($logger);
        $corsMiddleware = new CorsMiddleware($logger);

        $this->setupStrategy($container);

        $this->map('OPTIONS', '/{any:.*}', function (ServerRequestInterface $request): ResponseInterface {
            $response = new \GuzzleHttp\Psr7\Response;
            return $response
                ->withStatus(200)
                ->withHeader('Access-Control-Allow-Methods', 'GET,POST,PUT,DELETE,PATCH')
                ->withHeader('Access-Control-Allow-Headers', 'Authorization')
                ->withHeader('Access-Control-Allow-Origin', '*');
        });

        $this->map('POST', '/users/login', UsersLoginController::class)
            ->middleware($corsMiddleware);
        $this->group('', function (RouteGroup $router): void {
            (new TasksRouter)->mapRoutes($router);
            (new ClientsRouter)->mapRoutes($router);
            (new UsersRouter)->mapRoutes($router);
            (new ReportsRouter)->mapRoutes($router);
            (new VulnerabilitiesRouter)->mapRoutes($router);
            (new AuditLogRouter)->mapRoutes($router);
            (new ProjectsRouter)->mapRoutes($router);
            (new TargetsRouter)->mapRoutes($router);
            (new IntegrationsRouter)->mapRoutes($router);
        })
            ->middleware($corsMiddleware)
            ->middleware($authMiddleware);
    }
}
