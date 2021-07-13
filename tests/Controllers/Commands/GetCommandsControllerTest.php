<?php declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Repositories\CommandRepository;

class GetCommandsControllerTest extends TestCase
{
    public function testHappyPath()
    {
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->once())
            ->method('getQueryParams')
            ->willReturn(['limit' => 5, 'keywords' => 'foo']);

        $mockRepository = $this->createMock(CommandRepository::class);
        $mockRepository->expects($this->once())
            ->method('findByKeywords')
            ->with('foo', 5)
            ->willReturn([]);

        $controller = new GetCommandsController($mockRepository);
        $response = $controller($mockRequest);

        $this->assertEquals([], $response);
    }
}
