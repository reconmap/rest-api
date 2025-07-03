<?php declare(strict_types=1);

namespace Reconmap;

use GuzzleHttp\Psr7\HttpFactory;
use League\Route\RouteGroup;
use League\Route\Router;
use OpenApi\Attributes\Info;
use OpenApi\Attributes\OpenApi;
use OpenApi\Attributes\SecurityScheme;
use OpenApi\Attributes\Server;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Reconmap\{Controllers\Attachments\ServeAttachmentController,
    Controllers\AuditLog\AuditLogRouter,
    Controllers\Auth\AuthRouter,
    Controllers\Clients\ClientsRouter,
    Controllers\Commands\CommandsRouter,
    Controllers\Contacts\ContactsRouter,
    Controllers\Documents\DocumentsRouter,
    Controllers\Notes\NotesRouter,
    Controllers\Notifications\NotificationsRouter,
    Controllers\ProjectCategories\ProjectCategoriesRouter,
    Controllers\Projects\ProjectsRouter,
    Controllers\Reports\ReportsRouter,
    Controllers\System\CustomFields\CustomFieldsRouter,
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
use Reconmap\Controllers\Attachments\AttachmentsRouter;

#[OpenApi(
    info: new Info(version: "1.5.0", description: "Welcome to the Reconmap REST API.", title: "Reconmap API"),
    servers: [new Server("http://localhost:5510", "Local server")],
    security: [["bearerAuth" => []]],
)]
#[SecurityScheme(securityScheme: "bearerAuth", type: "http", name: "bearerAuth", in: "header", bearerFormat: "JWT", scheme: "bearer")]
class ApiRouter extends Router
{
    private const array ROUTER_CLASSES = [
        AuthRouter::class,
        AttachmentsRouter::class,
        AuditLogRouter::class,
        CommandsRouter::class,
        ContactsRouter::class,
        ClientsRouter::class,
        DocumentsRouter::class,
        NotesRouter::class,
        NotificationsRouter::class,
        ProjectsRouter::class,
        ProjectCategoriesRouter::class,
        ReportsRouter::class,
        SystemRouter::class,
        CustomFieldsRouter::class,
        TargetsRouter::class,
        TasksRouter::class,
        UsersRouter::class,
        VaultRouter::class,
        VulnerabilitiesRouter::class,
    ];

    public function mapRoutes(ContainerInterface $container, ApplicationConfig $applicationConfig): void
    {
        $responseFactory = new HttpFactory();

        $corsResponseDecorator = $container->get(CorsResponseDecorator::class);
        $logger = $container->get(LoggerInterface::class);

        $strategy = new ApiStrategy($responseFactory, $corsResponseDecorator, $logger);
        $strategy->setContainer($container);

        $this->setStrategy($strategy);

        $corsMiddleware = $container->get(CorsMiddleware::class);
        $this->prependMiddleware($corsMiddleware);

        $authMiddleware = $container->get(AuthMiddleware::class);
        $securityMiddleware = $container->get(SecurityMiddleware::class);
        $cookieMiddleware = $container->get(StaticMiddleware::class);

        $this->map('GET', '/image/{attachmentId:number}', ServeAttachmentController::class)->middlewares([$cookieMiddleware]);
        $this->map('GET', '/openapi.json', GetOpenApiYamlController::class);

        $this->group('', function (RouteGroup $router): void {
            foreach (self::ROUTER_CLASSES as $mappable) {
                (new $mappable)->mapRoutes($router);
            }
        })->middlewares([$securityMiddleware, $authMiddleware]);
    }
}
