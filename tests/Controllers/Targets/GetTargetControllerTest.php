<?php declare(strict_types=1);

namespace Reconmap\Controllers\Targets;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Repositories\TargetRepository;

class GetTargetControllerTest extends TestCase
{
    public function testHappyPath()
    {
        $mockTarget = ['name' => 'foo'];

        $mockRequest = $this->createMock(ServerRequestInterface::class);

        $mockRepository = $this->createMock(TargetRepository::class);
        $mockRepository->expects($this->once())
            ->method('findById')
            ->with(568)
            ->willReturn($mockTarget);

        $args = ['targetId' => 568];

        $controller = new GetTargetController($mockRepository);
        $response = $controller($mockRequest, $args);

        $this->assertEquals($mockTarget, $response);
    }
}
