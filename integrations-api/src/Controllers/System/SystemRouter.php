<?php

declare(strict_types=1);

namespace Reconmap\Controllers\System;

use League\Route\RouteCollectionInterface;

class SystemRouter
{

    public function mapRoutes(RouteCollectionInterface $router): void
    {
        $router->map('GET', '/system/health', GetSystemHealthController::class);
        $router->map('GET', '/system/integrations', GetIntegrationsController::class);
    }
}
