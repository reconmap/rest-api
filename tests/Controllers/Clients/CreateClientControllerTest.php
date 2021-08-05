<?php declare(strict_types=1);

namespace Reconmap\Controllers\Clients;

use Fig\Http\Message\StatusCodeInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Models\Client;
use Reconmap\Repositories\ClientRepository;

class CreateClientControllerTest extends TestCase
{
    public function testHappyPath()
    {
        $expectedClient = new Client();
        $expectedClient->name = 'exciting new client';
        $expectedClient->creator_uid = 9;

        $mockProjectRepository = $this->createMock(ClientRepository::class);
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

        $controller = new CreateClientController($mockProjectRepository);
        $response = $controller($mockRequest);

        $this->assertEquals(StatusCodeInterface::STATUS_CREATED, $response->getStatusCode());
    }
}
