<?php

declare(strict_types=1);

namespace Reconmap;

use League\Route\RouteCollectionInterface;
use Reconmap\Controllers\Reports\DeleteReportController;
use Reconmap\Controllers\Reports\GenerateReportController;
use Reconmap\Controllers\Reports\DownloadReportController;
use Reconmap\Controllers\Reports\GetReportsController;

class ReportsRouter
{

    public function mapRoutes(RouteCollectionInterface $router): void
    {
        $router->map('GET', '/reports', GetReportsController::class);
        $router->map('GET', '/reports/{id:number}/download', DownloadReportController::class);
        $router->map('GET', '/projects/{id:number}/report', GenerateReportController::class);
        $router->map('DELETE', '/reports/{id:number}', DeleteReportController::class);
    }
}
