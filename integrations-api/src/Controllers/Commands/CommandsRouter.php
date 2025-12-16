<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use League\Route\RouteCollectionInterface;

class CommandsRouter
{
    public function mapRoutes(RouteCollectionInterface $router): void
    {
        $router->map('PUT', '/commands/{commandId:number}', UpdateCommandController::class);
        $router->map('POST', '/commands/{commandId:number}/schedule', AddCommandScheduleController::class);
        $router->map('GET', '/commands/output-parsers', GetCommandOutputParsersController::class);
        $router->map('POST', '/commands/outputs', UploadCommandOutputController::class);
    }
}
