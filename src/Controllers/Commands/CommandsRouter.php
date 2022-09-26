<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use League\Route\RouteCollectionInterface;

class CommandsRouter
{
    public function mapRoutes(RouteCollectionInterface $router): void
    {
        $router->map('GET', '/commands/{commandId:number}', GetCommandController::class);
        $router->map('GET', '/commands/schedules', GetCommandsSchedulesController::class);
        $router->map('GET', '/commands/{commandId:number}/schedules', GetCommandSchedulesController::class);
        $router->map('DELETE', '/commands/schedules/{commandScheduleId:number}', DeleteCommandScheduleController::class);
        $router->map('PUT', '/commands/{commandId:number}', UpdateCommandController::class);
        $router->map('POST', '/commands/{commandId:number}/schedule', AddCommandScheduleController::class);
        $router->map('DELETE', '/commands/{commandId:number}', DeleteCommandController::class);
        $router->map('GET', '/commands/output-parsers', GetCommandOutputParsersController::class);
        $router->map('POST', '/commands', CreateCommandController::class);
        $router->map('GET', '/commands', GetCommandsController::class);
        $router->map('POST', '/commands/outputs', UploadCommandOutputController::class);
    }
}
