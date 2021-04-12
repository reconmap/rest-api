<?php declare(strict_types=1);

namespace Reconmap\Controllers\Targets;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Repositories\TargetRepository;

class GetTargetsControllerTest extends TestCase
{
    public function testHappyPath()
    {
        $mockParams = ['projectId' => 5];

        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->once())
            ->method('getQueryParams')
            ->willReturn($mockParams);

        $expectedTarget = ['id' => 5];

        $mockTargetRepository = $this->createMock(TargetRepository::class);
        $mockTargetRepository->expects($this->once())
            ->method('findByProjectId')
            ->willReturn($expectedTarget);

        $controller = new GetTargetsController($mockTargetRepository);
        $target = $controller($mockRequest);

        $this->assertEquals($expectedTarget, $target);
    }
}
