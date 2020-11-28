<?php
declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use League\Route\RouteCollectionInterface;
use PHPUnit\Framework\TestCase;

class ProjectsRouterTest extends TestCase
{
    public function testAtLeastOneRouteIsMapped()
    {
        $routeCollectionMock = $this->createMock(RouteCollectionInterface::class);
        $routeCollectionMock->expects($this->atLeast(12))
            ->method('map');

        $router = new ProjectsRouter();
        $router->mapRoutes($routeCollectionMock);
    }
}
