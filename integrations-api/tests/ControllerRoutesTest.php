<?php

declare(strict_types=1);

namespace Reconmap;

use League\Route\RouteCollectionInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Reconmap\Controllers\Attachments\AttachmentsRouter;
use Reconmap\Controllers\Commands\CommandsRouter;
use Reconmap\Controllers\Reports\ReportsRouter;
use Reconmap\Controllers\System\SystemRouter;
use Reconmap\Controllers\Users\UsersRouter;
use Reconmap\Controllers\Vulnerabilities\VulnerabilitiesRouter;

class ControllerRoutesTest extends TestCase
{
    public static function routerDataProvider(): array
    {
        return [
            [AttachmentsRouter::class],
            [CommandsRouter::class],
            [ReportsRouter::class],
            [SystemRouter::class],
            [UsersRouter::class],
            [VulnerabilitiesRouter::class]
        ];
    }

    #[DataProvider("routerDataProvider")]
    public function testAllRouters(string $routerClass)
    {
        $mockCollection = $this->createMock(RouteCollectionInterface::class);
        $mockCollection->expects($this->atLeastOnce())
            ->method('map')
            ->with(
                $this->logicalOr(
                    $this->equalTo('GET'),
                    $this->equalTo('PUT'),
                    $this->equalTo('POST'),
                    $this->equalTo('PATCH'),
                    $this->equalTo('DELETE'),
                ),
                $this->isType('string'),
                $this->isType('string')
            );

        $router = new $routerClass;
        $router->mapRoutes($mockCollection);
    }
}
