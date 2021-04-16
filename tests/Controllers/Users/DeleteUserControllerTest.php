<?php declare(strict_types=1);

namespace Reconmap\Controllers\Users;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Commands\DeleteCommandController;
use Reconmap\Repositories\CommandRepository;

class DeleteUserControllerTest extends TestCase
{
    public function testSuccessfulDelete()
    {
        $fakeCommandId = 86;

        $mockCommandRepository = $this->createMock(CommandRepository::class);
        $mockCommandRepository->expects($this->once())
            ->method('deleteById')
            ->with($fakeCommandId)
            ->willReturn(true);

        $mockRequest = $this->createMock(ServerRequestInterface::class);

        $args = ['commandId' => $fakeCommandId];

        $controller = new DeleteCommandController($mockCommandRepository);
        $response = $controller($mockRequest, $args);

        $this->assertTrue($response['success']);
    }
}
