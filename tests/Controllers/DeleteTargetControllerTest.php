<?php declare(strict_types=1);

namespace Reconmap\Controllers\Targets;

use League\Plates\Engine;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\ControllerTestCase;

class DeleteTargetControllerTest extends ControllerTestCase
{

    public function testSuccess(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects($this->once())
            ->method('getAttribute')
            ->willReturn(1);
        $args = ['targetId' => 0];

        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $controller = $this->injectController(new DeleteTargetController());
        $response = $controller($request, $args);
        $this->assertEquals(['success' => 0], $response);
    }
}
