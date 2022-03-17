<?php declare(strict_types=1);

namespace Reconmap;

use Laminas\Diactoros\ResponseFactory;
use League\Container\Container;
use League\Route\RouteGroup;
use League\Route\Router;
use Monolog\Logger;
use Reconmap\{Controllers\AuditLog\AuditLogRouter,
    Controllers\Auth\AuthRouter,
    Controllers\Auth\LoginController,
    Controllers\Clients\ClientsRouter,
    Controllers\Commands\CommandsRouter,
    Controllers\Contacts\ContactsRouter,
    Controllers\Documents\DocumentsRouter,
    Controllers\Notes\NotesRouter,
    Controllers\Notifications\NotificationsRouter,
    Controllers\Organisations\OrganisationsRouter,
    Controllers\Projects\ProjectsRouter,
    Controllers\Reports\ReportsRouter,
    Controllers\System\SystemRouter,
    Controllers\Targets\TargetsRouter,
    Controllers\Tasks\TasksRouter,
    Controllers\Users\UsersRouter,
    Controllers\Vault\VaultRouter,
    Controllers\Vulnerabilities\VulnerabilitiesRouter,
    Http\AuthMiddleware,
    Http\CorsMiddleware,
    Http\SecurityMiddleware,
    Services\ApplicationConfig
};
use Reconmap\Controllers\Attachments\AttachmentsRouter;

class ApiRouter extends Router
{
    private const ROUTER_CLASSES = [
        AuthRouter::class,
        AttachmentsRouter::class,
        AuditLogRouter::class,
        CommandsRouter::class,
        ContactsRouter::class,
        ClientsRouter::class,
        DocumentsRouter::class,
        NotesRouter::class,
        NotificationsRouter::class,
        OrganisationsRouter::class,
        ProjectsRouter::class,
        ReportsRouter::class,
        SystemRouter::class,
        TargetsRouter::class,
        TasksRouter::class,
        UsersRouter::class,
        VaultRouter::class,
        VulnerabilitiesRouter::class,
    ];

    public function mapRoutes(Container $container, ApplicationConfig $applicationConfig): void
    {
        $this->setupStrategy($container, $applicationConfig);

        $corsMiddleware = $container->get(CorsMiddleware::class);
        $authMiddleware = $container->get(AuthMiddleware::class);
        $securityMiddleware = $container->get(SecurityMiddleware::class);

        $this->map('POST', '/users/login', LoginController::class)
            ->middleware($corsMiddleware);

        $this->group('', function (RouteGroup $router): void {
            foreach (self::ROUTER_CLASSES as $mappable) {
                (new $mappable)->mapRoutes($router);
            }
        })->middlewares([$corsMiddleware, $securityMiddleware, $authMiddleware]);
    }

    private function setupStrategy(Container $container, ApplicationConfig $applicationConfig)
    {
        $responseFactory = new ResponseFactory;

        $strategy = new ApiStrategy($responseFactory, $applicationConfig, $container->get(Logger::class));
        $strategy->setContainer($container);

        $this->setStrategy($strategy);
    }
}
