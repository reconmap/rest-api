<?php declare(strict_types=1);

namespace Reconmap\Controllers\Organisations;

use League\Route\RouteCollectionInterface;

class OrganisationsRouter
{
    public function mapRoutes(RouteCollectionInterface $router): void
    {
        $router->map('GET', '/organisations/root', GetOrganisationController::class);
        $router->map('PUT', '/organisations/root', UpdateOrganisationController::class);
    }
}
