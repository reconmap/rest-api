<?php

declare(strict_types=1);

namespace Reconmap;

use GuzzleHttp\Psr7\Response;
use Laminas\Diactoros\ResponseFactory;
use League\Container\Container;
use League\Route\RouteGroup;
use League\Route\Router;
use League\Route\Strategy\JsonStrategy;
use Monolog\Logger;
use Reconmap\{
    Controllers\AuditLog\AuditLogRouter,
    Controllers\Clients\ClientsRouter,
    Controllers\Commands\CommandsRouter,
    Controllers\Notes\NotesRouter,
    Controllers\Organisations\OrganisationsRouter,
    Controllers\Projects\ProjectsRouter,
    Controllers\Reports\ReportsRouter,
    Controllers\System\SystemRouter,
    Controllers\Targets\TargetsRouter,
    Controllers\Tasks\TasksRouter,
    Controllers\Users\UsersLoginController,
    Controllers\Users\UsersRouter,
    Controllers\Vulnerabilities\VulnerabilitiesRouter
};
use Reconmap\Controllers\Attachments\AttachmentsRouter;

class ApiRouter extends Router
{
    private const ROUTER_CLASSES = [
        AttachmentsRouter::class,
        AuditLogRouter::class,
        CommandsRouter::class,
        ClientsRouter::class,
        NotesRouter::class,
        OrganisationsRouter::class,
        ProjectsRouter::class,
        ReportsRouter::class,
        SystemRouter::class,
        TargetsRouter::class,
        TasksRouter::class,
        UsersRouter::class,
        VulnerabilitiesRouter::class,
    ];

    private Logger $logger;

    private AuthMiddleware $authMiddleware;

    private CorsMiddleware $corsMiddleware;

    public function mapRoutes(Container $container, Logger $logger): void
    {
        $this->setupStrategy($container, $logger);
        $this->map('OPTIONS', '/{any:.*}', function (): Response {
            return $this->getResponse();
        });

        $this->map('POST', '/users/login', UsersLoginController::class)
            ->middleware($this->corsMiddleware);
        $this->group('', function (RouteGroup $router): void {
            foreach (self::ROUTER_CLASSES as $mappable) {
                (new $mappable)->mapRoutes($router);
            }
        })->middlewares([$this->corsMiddleware, $this->authMiddleware]);
    }

    /**
     * @param Container $container
     * @param Logger $logger
     */
    private function setupStrategy(Container $container, Logger $logger)
    {
        $responseFactory = new ResponseFactory;
        $strategy = new JsonStrategy($responseFactory);
        $strategy->setContainer($container);
        $this->setStrategy($strategy);
        $this->logger = $logger;
        $this->authMiddleware = new AuthMiddleware($logger);
        $this->corsMiddleware = new CorsMiddleware($logger);
    }

    private function getResponse(): Response
    {
        return (new Response)
            ->withStatus(200)
            ->withHeader('Access-Control-Allow-Methods', 'GET,POST,PUT,DELETE,PATCH')
            ->withHeader('Access-Control-Allow-Headers', 'Authorization,Bulk-Operation,Content-Type')
            ->withHeader('Access-Control-Allow-Origin', '*');
    }
}
