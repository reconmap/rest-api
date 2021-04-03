<?php declare(strict_types=1);

namespace Reconmap\Controllers\Users;

use League\Route\RouteCollectionInterface;
use PHPUnit\Framework\TestCase;

class UsersRouterTest extends TestCase
{
    public function testAtLeastOneRouteIsMapped()
    {
        $routeCollectionMock = $this->createMock(RouteCollectionInterface::class);
        $routeCollectionMock->expects($this->atLeastOnce())
            ->method('map');

        $router = new UsersRouter();
        $router->mapRoutes($routeCollectionMock);
    }
}
