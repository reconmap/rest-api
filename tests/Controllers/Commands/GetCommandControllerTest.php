<?php declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Repositories\CommandRepository;

class GetCommandControllerTest extends TestCase
{
    public function testHappyPath()
    {
        $mockDocument = ['title' => 'foo'];

        $mockRequest = $this->createMock(ServerRequestInterface::class);

        $mockRepository = $this->createMock(CommandRepository::class);
        $mockRepository->expects($this->once())
            ->method('findById')
            ->with(568)
            ->willReturn($mockDocument);

        $args = ['commandId' => 568];

        $controller = new GetCommandController($mockRepository);
        $response = $controller($mockRequest, $args);

        $this->assertEquals($mockDocument, $response);
    }
}
