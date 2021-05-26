<?php declare(strict_types=1);

namespace Reconmap;

use Monolog\Logger;
use Reconmap\Controllers\Controller;

class ControllerTestCase extends DatabaseTestCase
{
    public function injectController(Controller $controller): Controller
    {
        $mockLogger = $this->createMock(Logger::class);
        $controller->setLogger($mockLogger);

        return $controller;
    }
}
