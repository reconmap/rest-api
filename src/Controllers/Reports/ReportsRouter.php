<?php declare(strict_types=1);

namespace Reconmap\Controllers\Reports;

use League\Route\RouteCollectionInterface;

class ReportsRouter
{

    public function mapRoutes(RouteCollectionInterface $router): void
    {
        $router->map('GET', '/reports', GetReportsController::class);
        $router->map('GET', '/reports/preview', GetReportPreviewController::class);
        $router->map('GET', '/reports/{projectId:number}/configuration', GetReportConfigurationController::class);
        $router->map('PUT', '/reports/{projectId:number}/configuration', ReplaceReportConfigurationController::class);
        $router->map('POST', '/reports/{reportId:number}/send', SendReportController::class);
        $router->map('POST', '/reports', CreateReportController::class);
        $router->map('DELETE', '/reports/{id:number}', DeleteReportController::class);
    }
}
