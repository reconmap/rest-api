<?php
declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use League\Route\RouteCollectionInterface;
use PHPUnit\Framework\TestCase;

class TasksRouterTest extends TestCase
{
    public function testAtLeastOneRouteIsMapped()
    {
        $routeCollectionMock = $this->createMock(RouteCollectionInterface::class);
        $routeCollectionMock->expects($this->atLeastOnce())
            ->method('map');

        $router = new TasksRouter();
        $router->mapRoutes($routeCollectionMock);
    }
}
