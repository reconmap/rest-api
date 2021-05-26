<?php declare(strict_types=1);

namespace Reconmap\Controllers\Targets;

use League\Route\RouteCollectionInterface;

class TargetsRouter
{

    public function mapRoutes(RouteCollectionInterface $router): void
    {
        $router->map('POST', '/targets', CreateTargetController::class);
        $router->map('PATCH', '/targets', BulkUpdateTargetsController::class);
        $router->map('GET', '/targets', GetTargetsController::class);
        $router->map('GET', '/targets/{targetId:number}', GetTargetController::class);
        $router->map('DELETE', '/targets/{targetId:number}', DeleteTargetController::class);
    }
}
