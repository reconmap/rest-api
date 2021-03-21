<?php declare(strict_types=1);

namespace Reconmap;

use Monolog\Logger;
use Reconmap\Controllers\Controller;
use Reconmap\Services\TemplateEngine;

class ControllerTestCase extends DatabaseTestCase
{
    public function injectController(Controller $controller): Controller
    {
        $mockLogger = $this->createMock(Logger::class);
        $controller->setLogger($mockLogger);

        $mockTemplate = $this->createMock(TemplateEngine::class);
        $controller->setTemplate($mockTemplate);

        $controller->setDb($this->getDatabaseConnection());

        return $controller;
    }
}
