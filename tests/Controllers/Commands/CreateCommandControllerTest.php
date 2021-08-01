<?php declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Models\Command;
use Reconmap\Repositories\CommandRepository;

class CreateCommandControllerTest extends TestCase
{
    public function testHappyPath()
    {
        $expectedCommand = new Command();
        $expectedCommand->short_name = 'co_mmand';
        $expectedCommand->creator_uid = 9;

        $mockProjectRepository = $this->createMock(CommandRepository::class);
        $mockProjectRepository->expects($this->once())
            ->method('insert')
            ->with($expectedCommand)
            ->willReturn(1);

        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->once())
            ->method('getAttribute')
            ->with('userId')
            ->willReturn(9);
        $mockRequest->expects($this->once())
            ->method('getBody')
            ->willReturn('{"short_name": "co_mmand"}');

        $controller = new CreateCommandController($mockProjectRepository);
        $response = $controller($mockRequest);

        $this->assertEquals(1, $response['success']);
    }
}
