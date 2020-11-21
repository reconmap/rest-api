<?php declare(strict_types=1);

namespace Reconmap\Controllers\Vulnerabilities;

use League\Route\RouteCollectionInterface;
use PHPUnit\Framework\TestCase;

class VulnerabilitiesRouterTest extends TestCase
{
    public function testAtLeastOneRouteIsMapped()
    {
        $routeCollectionMock = $this->createMock(RouteCollectionInterface::class);
        $routeCollectionMock->expects($this->atLeast(7))
            ->method('map');

        $router = new VulnerabilitiesRouter();
        $router->mapRoutes($routeCollectionMock);
    }
}
