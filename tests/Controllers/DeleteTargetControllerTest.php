<?php declare(strict_types=1);

namespace Reconmap\Controllers\Targets;

use League\Plates\Engine;
use Monolog\Logger;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\DatabaseTestCase;
use Reconmap\Services\TemplateEngine;

class DeleteTargetControllerTest extends DatabaseTestCase
{

    public function testSuccess(): void
    {
        /** @var Logger|MockObject */
        $logger = $this->createMock(Logger::class);
        /** @var Engine|MockObject */
        $template = $this->createMock(TemplateEngine::class);
        $db = $this->getDatabaseConnection();

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects($this->once())
            ->method('getAttribute')
            ->willReturn(1);
        $args = ['targetId' => 0];

        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $controller = new DeleteTargetController($logger, $db, $template);
        $response = $controller($request, $args);
        $this->assertEquals(['success' => 0], $response);
    }
}
