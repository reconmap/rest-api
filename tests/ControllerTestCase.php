<?php declare(strict_types=1);

namespace Reconmap;

use Monolog\Logger;
use Reconmap\Controllers\Controller;
use Reconmap\Services\Security\AuthorisationService;

class ControllerTestCase extends DatabaseTestCase
{
    public function injectController(Controller $controller): Controller
    {
        $mockLogger = $this->createMock(Logger::class);
        $controller->setLogger($mockLogger);

        return $controller;
    }

    public function createAuthorisationServiceMock(): AuthorisationService
    {
        $mockAuthorisationService = $this->createMock(AuthorisationService::class);
        $mockAuthorisationService->expects($this->once())
            ->method('isRoleAllowed')
            ->willReturn(true);
        return $mockAuthorisationService;
    }
}
