<?php declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Repositories\CommandRepository;

class CreateCommandControllerTest extends TestCase
{
    public function testHappyPath()
    {
        $expectedClient = new \stdClass();
        $expectedClient->name = 'exciting new client';
        $expectedClient->creator_uid = 9;

        $mockProjectRepository = $this->createMock(CommandRepository::class);
        $mockProjectRepository->expects($this->once())
            ->method('insert')
            ->with($expectedClient)
            ->willReturn(1);

        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->once())
            ->method('getAttribute')
            ->with('userId')
            ->willReturn(9);
        $mockRequest->expects($this->once())
            ->method('getBody')
            ->willReturn('{"name": "exciting new client"}');

        $controller = new CreateCommandController($mockProjectRepository);
        $response = $controller($mockRequest);

        $this->assertEquals(1, $response['success']);
    }
}
