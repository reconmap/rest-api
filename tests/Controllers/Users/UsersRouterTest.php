<?php
declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use League\Route\RouteCollectionInterface;
use PHPUnit\Framework\TestCase;
use Reconmap\Controllers\Users\UsersRouter;

class UsersRouterTest extends TestCase
{
    public function testAtLeastOneRouteIsMapped()
    {
        $routeCollectionMock = $this->createMock(RouteCollectionInterface::class);
        $routeCollectionMock->expects($this->atLeast(9))
            ->method('map');

        $router = new UsersRouter();
        $router->mapRoutes($routeCollectionMock);
    }
}
