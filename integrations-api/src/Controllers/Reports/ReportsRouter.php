<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Reports;

use League\Route\RouteCollectionInterface;

class ReportsRouter
{

    public function mapRoutes(RouteCollectionInterface $router): void
    {
        $router->map('POST', '/reports/templates', CreateReportTemplateController::class);
        $router->map('POST', '/reports/{reportId:number}/send', SendReportController::class);
        $router->map('POST', '/reports', CreateReportController::class);
    }
}
