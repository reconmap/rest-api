<?php declare(strict_types=1);

namespace Reconmap\Controllers\Vulnerabilities;

use League\Route\RouteCollectionInterface;
use Reconmap\Controllers\Vulnerabilities\Categories\CreateVulnerabilityCategoryController;
use Reconmap\Controllers\Vulnerabilities\Categories\DeleteVulnerabilityCategoryController;
use Reconmap\Controllers\Vulnerabilities\Categories\GetVulnerabilityCategoriesController;

class VulnerabilitiesRouter
{
    public function mapRoutes(RouteCollectionInterface $router): void
    {
        /** Categories */
        $router->map('POST', '/vulnerabilities/categories', CreateVulnerabilityCategoryController::class);
        $router->map('GET', '/vulnerabilities/categories', GetVulnerabilityCategoriesController::class);
        $router->map('DELETE', '/vulnerabilities/categories/{categoryId:number}', DeleteVulnerabilityCategoryController::class);

        /** Vulnerabilities */
        $router->map('GET', '/vulnerabilities', GetVulnerabilitiesController::class);
        $router->map('POST', '/vulnerabilities', CreateVulnerabilityController::class);
        $router->map('PATCH', '/vulnerabilities', BulkUpdateVulnerabilitiesController::class);
        $router->map('GET', '/vulnerabilities/stats', GetVulnerabilitiesStatsController::class);
        $router->map('GET', '/vulnerabilities/{vulnerabilityId:number}', GetVulnerabilityController::class);
        $router->map('POST', '/vulnerabilities/{vulnerabilityId:number}/clone', CloneVulnerabilityController::class);
        $router->map('PUT', '/vulnerabilities/{vulnerabilityId:number}', UpdateVulnerabilityController::class);
        $router->map('PATCH', '/vulnerabilities/{vulnerabilityId:number}', UpdateVulnerabilityController::class);
        $router->map('DELETE', '/vulnerabilities/{vulnerabilityId:number}', DeleteVulnerabilityController::class);
    }
}
