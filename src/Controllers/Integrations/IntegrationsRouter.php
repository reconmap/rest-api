<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Integrations;

use League\Route\RouteCollectionInterface;

class IntegrationsRouter
{

    public function mapRoutes(RouteCollectionInterface $router): void
    {
        $router->map('GET', '/integrations', GetIntegrationsController::class);
    }
}
