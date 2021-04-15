<?php declare(strict_types=1);

namespace Reconmap\Controllers\Clients;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Repositories\ClientRepository;

class GetClientsControllerTest extends TestCase
{
    public function testHappyPath()
    {
        $mockRequest = $this->createMock(ServerRequestInterface::class);

        $mockRepository = $this->createMock(ClientRepository::class);
        $mockRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([]);

        $controller = new GetClientsController($mockRepository);
        $response = $controller($mockRequest);

        $this->assertEquals([], $response);
    }
}
