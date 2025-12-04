<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Vulnerabilities;

use League\Route\RouteCollectionInterface;
use Reconmap\Controllers\Vulnerabilities\Categories\GetVulnerabilityChildrenCategoriesController;
use Reconmap\Controllers\Vulnerabilities\Categories\UpdateVulnerabilityCategoryController;

class VulnerabilitiesRouter
{
    public function mapRoutes(RouteCollectionInterface $router): void
    {
        /** Categories */
        $router->map('GET', '/vulnerabilities/categories/{categoryId:number}', GetVulnerabilityChildrenCategoriesController::class);
        $router->map('PUT', '/vulnerabilities/categories/{categoryId:number}', UpdateVulnerabilityCategoryController::class);

        /** Vulnerabilities */
        $router->map('GET', '/vulnerabilities', GetVulnerabilitiesController::class);
        $router->map('POST', '/vulnerabilities', CreateVulnerabilityController::class);
        $router->map('PATCH', '/vulnerabilities', BulkUpdateVulnerabilitiesController::class);
        $router->map('GET', '/vulnerabilities/stats', GetVulnerabilitiesStatsController::class);
        $router->map('GET', '/vulnerabilities/{vulnerabilityId:number}', GetVulnerabilityController::class);
        $router->map('PUT', '/vulnerabilities/{vulnerabilityId:number}/remediation', GenerateVulnerabilityRemediationController::class);
        $router->map('POST', '/vulnerabilities/{vulnerabilityId:number}/clone', CloneVulnerabilityController::class);
        $router->map('PUT', '/vulnerabilities/{vulnerabilityId:number}', UpdateVulnerabilityController::class);
        $router->map('PATCH', '/vulnerabilities/{vulnerabilityId:number}', UpdateVulnerabilityController::class);
    }
}
