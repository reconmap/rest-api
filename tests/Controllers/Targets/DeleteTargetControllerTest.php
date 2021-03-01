<?php declare(strict_types=1);

namespace Reconmap\Controllers\Targets;

use League\Plates\Engine;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\ControllerTestCase;
use Reconmap\Repositories\TargetRepository;

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

        $mockRepository = $this->createPartialMock(TargetRepository::class, ['deleteById']);
        $mockRepository->expects($this->once())
            ->method('deleteById')
            ->with(0)
            ->willReturn(true);

        $controller = $this->injectController(new DeleteTargetController($mockRepository));
        $response = $controller($request, $args);
        $this->assertEquals(['success' => true], $response);
    }
}
