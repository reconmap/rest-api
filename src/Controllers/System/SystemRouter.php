<?php declare(strict_types=1);

namespace Reconmap\Controllers\System;

use League\Route\RouteCollectionInterface;

class SystemRouter
{

    public function mapRoutes(RouteCollectionInterface $router): void
    {
        $router->map('GET', '/recent-searches', GetRecentSearchesController::class);
        $router->map('GET', '/system/health', GetSystemHealthController::class);
        $router->map('GET', '/system/integrations', GetIntegrationsController::class);
        $router->map('GET', '/system/exportables', GetExportablesController::class);
        $router->map('GET', '/system/usage', GetSystemUsageController::class);
        $router->map('GET', '/system/data', ExportDataController::class);
        $router->map('POST', '/system/data', ImportDataController::class);
    }
}
