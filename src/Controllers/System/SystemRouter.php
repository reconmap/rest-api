<?php declare(strict_types=1);

namespace Reconmap\Controllers\System;

use League\Route\RouteCollectionInterface;

class SystemRouter
{

    public function mapRoutes(RouteCollectionInterface $router): void
    {
        $router->get('/system/health', GetHealthController::class);
        $router->get('/recent-searches', GetRecentSearchesController::class);
        $router->map('GET', '/system/integrations', GetIntegrationsController::class);
        $router->map('POST', '/system/data', ImportDataController::class);
        $router->map('GET', '/system/data', ExportDataController::class);
        $router->map('GET', '/system/exportables', GetExportablesController::class);
        $router->map('GET', '/system/usage', GetSystemUsageController::class);
    }
}
