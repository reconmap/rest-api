<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Reports;

use League\Route\RouteCollectionInterface;

class ReportsRouter
{

    public function mapRoutes(RouteCollectionInterface $router): void
    {
        $router->map('GET', '/reports', GetReportsController::class);
        $router->map('GET', '/reports/{id:number}/download', DownloadReportController::class);
        $router->map('POST', '/reports/{reportId:number}/send', SendReportController::class);
        $router->map('GET', '/projects/{id:number}/report', GenerateReportController::class);
        $router->map('DELETE', '/reports/{id:number}', DeleteReportController::class);
    }
}
