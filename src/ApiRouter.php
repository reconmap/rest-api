<?php declare(strict_types=1);

namespace Reconmap;

use Laminas\Diactoros\ResponseFactory;
use League\Container\Container;
use League\Route\RouteGroup;
use League\Route\Router;
use Monolog\Logger;
use Reconmap\{Controllers\Attachments\ServeAttachmentController,
    Controllers\AuditLog\AuditLogRouter,
    Controllers\Auth\AuthRouter,
    Controllers\Clients\ClientsRouter,
    Controllers\Commands\CommandsRouter,
    Controllers\Contacts\ContactsRouter,
    Controllers\Documents\DocumentsRouter,
    Controllers\Notes\NotesRouter,
    Controllers\Notifications\NotificationsRouter,
    Controllers\Organisations\OrganisationsRouter,
    Controllers\ProjectCategories\ProjectCategoriesRouter,
    Controllers\Projects\ProjectsRouter,
    Controllers\Reports\ReportsRouter,
    Controllers\System\GetOpenApiYamlController,
    Controllers\System\SystemRouter,
    Controllers\Targets\TargetsRouter,
    Controllers\Tasks\TasksRouter,
    Controllers\Users\UsersRouter,
    Controllers\Vault\VaultRouter,
    Controllers\Vulnerabilities\VulnerabilitiesRouter,
    Http\AuthMiddleware,
    Http\CorsMiddleware,
    Http\CorsResponseDecorator,
    Http\SecurityMiddleware,
    Http\StaticMiddleware,
    Services\ApplicationConfig};
use OpenApi\Attributes\Info;
use OpenApi\Attributes\SecurityScheme;
use Reconmap\Controllers\Attachments\AttachmentsRouter;

#[Info(version: "1.0.0", description: "Reconmap REST API", title: "Reconmap API")]
#[SecurityScheme(securityScheme: "bearerAuth", type: "http", bearerFormat: "JWT", scheme: "bearer")]
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
        ProjectCategoriesRouter::class,
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
        $responseFactory = new ResponseFactory;

        $corsResponseDecorator = $container->get(CorsResponseDecorator::class);
        $logger = $container->get(Logger::class);

        $strategy = new ApiStrategy($responseFactory, $corsResponseDecorator, $logger);
        $strategy->setContainer($container);

        $this->setStrategy($strategy);

        $corsMiddleware = $container->get(CorsMiddleware::class);
        $this->prependMiddleware($corsMiddleware);

        $authMiddleware = $container->get(AuthMiddleware::class);
        $securityMiddleware = $container->get(SecurityMiddleware::class);
        $cookieMiddleware = $container->get(StaticMiddleware::class);

        $this->map('GET', '/openapi.json', GetOpenApiYamlController::class);
        $this->map('GET', '/image/{attachmentId:number}', ServeAttachmentController::class)->middlewares([$cookieMiddleware]);

        $this->group('', function (RouteGroup $router): void {
            foreach (self::ROUTER_CLASSES as $mappable) {
                (new $mappable)->mapRoutes($router);
            }
        })->middlewares([$securityMiddleware, $authMiddleware]);
    }
}
