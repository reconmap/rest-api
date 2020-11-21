<?php declare(strict_types=1);

namespace Reconmap\Controllers\Vulnerabilities;

use League\Route\RouteCollectionInterface;

class VulnerabilitiesRouter
{

    public function mapRoutes(RouteCollectionInterface $router): void
    {
        $router->map('GET', '/vulnerabilities', GetVulnerabilitiesController::class);
        $router->map('GET', '/vulnerabilities/categories', GetVulnerabilityCategoriesController::class);
        $router->map('POST', '/vulnerabilities', CreateVulnerabilityController::class);
        $router->map('GET', '/vulnerabilities/stats', GetVulnerabilitiesStatsController::class);
        $router->map('GET', '/vulnerabilities/{id:number}', GetVulnerabilityController::class);
        $router->map('PATCH', '/vulnerabilities/{id:number}', UpdateVulnerabilityController::class);
        $router->map('DELETE', '/vulnerabilities/{id:number}', DeleteVulnerabilityController::class);
    }
}
