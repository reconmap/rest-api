<?php declare(strict_types=1);

namespace Reconmap\Controllers\Clients;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Models\Client;
use Reconmap\Repositories\ClientRepository;

class GetClientControllerTest extends TestCase
{
    public function testHappyPath()
    {
        $mockClient = $this->createMock(Client::class);

        $mockRequest = $this->createMock(ServerRequestInterface::class);

        $mockRepository = $this->createMock(ClientRepository::class);
        $mockRepository->expects($this->once())
            ->method('findById')
            ->with(568)
            ->willReturn($mockClient);

        $args = ['clientId' => 568];

        $controller = new GetClientController($mockRepository);
        $response = $controller($mockRequest, $args);

        $this->assertEquals($mockClient, $response);
    }
}
