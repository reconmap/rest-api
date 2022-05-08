<?php declare(strict_types=1);

namespace Reconmap\Controllers\ProjectCategories;

use League\Route\RouteCollectionInterface;

class ProjectCategoriesRouter
{
    public function mapRoutes(RouteCollectionInterface $router): void
    {
        $router->get('/project/categories', GetProjectCategoriesController::class);
    }
}
