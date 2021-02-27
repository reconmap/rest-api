<?php declare(strict_types=1);

namespace Reconmap;

use Laminas\Diactoros\ResponseFactory;
use League\Container\Container;
use League\Route\RouteGroup;
use League\Route\Router;
use Reconmap\{Controllers\AuditLog\AuditLogRouter,
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
    Controllers\Vulnerabilities\VulnerabilitiesRouter,
    Services\ApplicationConfig
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

    public function mapRoutes(Container $container, ApplicationConfig $applicationConfig): void
    {
        $this->setupStrategy($container, $applicationConfig);

        $authMiddleware = $container->get(AuthMiddleware::class);
        $corsMiddleware = $container->get(CorsMiddleware::class);

        $this->map('POST', '/users/login', UsersLoginController::class)
            ->middleware($corsMiddleware);

        $this->group('', function (RouteGroup $router): void {
            foreach (self::ROUTER_CLASSES as $mappable) {
                (new $mappable)->mapRoutes($router);
            }
        })->middlewares([$corsMiddleware, $authMiddleware]);
    }

    private function setupStrategy(Container $container, ApplicationConfig $applicationConfig)
    {
        $responseFactory = new ResponseFactory;

        $strategy = new ApiStrategy($responseFactory);
        $strategy->setConfig($applicationConfig);
        $strategy->setContainer($container);

        $this->setStrategy($strategy);
    }
}
