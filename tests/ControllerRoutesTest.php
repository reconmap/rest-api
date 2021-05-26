<?php declare(strict_types=1);

namespace Reconmap;

use League\Route\RouteCollectionInterface;
use PHPUnit\Framework\TestCase;
use Reconmap\Controllers\Attachments\AttachmentsRouter;
use Reconmap\Controllers\AuditLog\AuditLogRouter;
use Reconmap\Controllers\Auth\AuthRouter;
use Reconmap\Controllers\Clients\ClientsRouter;
use Reconmap\Controllers\Commands\CommandsRouter;
use Reconmap\Controllers\Documents\DocumentsRouter;
use Reconmap\Controllers\Notes\NotesRouter;
use Reconmap\Controllers\Organisations\OrganisationsRouter;
use Reconmap\Controllers\Projects\ProjectsRouter;
use Reconmap\Controllers\Reports\ReportsRouter;
use Reconmap\Controllers\System\SystemRouter;
use Reconmap\Controllers\Targets\TargetsRouter;
use Reconmap\Controllers\Tasks\TasksRouter;
use Reconmap\Controllers\Users\UsersRouter;
use Reconmap\Controllers\Vulnerabilities\VulnerabilitiesRouter;

class ControllerRoutesTest extends TestCase
{
    public function routerDataProvider(): array
    {
        return [
            [AuthRouter::class],
            [AttachmentsRouter::class],
            [AuditLogRouter::class],
            [CommandsRouter::class],
            [ClientsRouter::class],
            [DocumentsRouter::class],
            [NotesRouter::class],
            [OrganisationsRouter::class],
            [ProjectsRouter::class],
            [ReportsRouter::class],
            [SystemRouter::class],
            [TargetsRouter::class],
            [TasksRouter::class],
            [UsersRouter::class],
            [VulnerabilitiesRouter::class]
        ];
    }

    /**
     * @param string $routerClass
     * @dataProvider routerDataProvider
     */
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
