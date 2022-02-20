<?php declare(strict_types=1);

namespace Reconmap\Controllers\Clients;

use Fig\Http\Message\StatusCodeInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Models\Client;
use Reconmap\Repositories\ClientContactRepository;
use Reconmap\Repositories\ClientRepository;
use Reconmap\Repositories\ContactRepository;

class CreateClientControllerTest extends TestCase
{
    public function testHappyPath()
    {
        $expectedClient = new Client();
        $expectedClient->name = 'exciting new client';
        $expectedClient->address = 'evergreen';
        $expectedClient->url = '1.1.1.1';
        $expectedClient->creator_uid = 9;

        $mockProjectRepository = $this->createMock(ClientRepository::class);
        $mockProjectRepository->expects($this->once())
            ->method('insert')
            ->with($expectedClient)
            ->willReturn(1);

        $mockContactRepository = $this->createMock(ContactRepository::class);

        $mockClientContactRepository = $this->createMock(ClientContactRepository::class);

        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->once())
            ->method('getAttribute')
            ->with('userId')
            ->willReturn(9);
        $mockRequest->expects($this->exactly(2))
            ->method('getBody')
            ->willReturn('{"name":"exciting new client","address":"evergreen","url":"1.1.1.1","contact_name":"elliot","contact_phone":"0","contact_email":"e@fsoc","contact_kind":"technical","contact_role":"ciso"}');

        $controller = new CreateClientController($mockProjectRepository, $mockContactRepository, $mockClientContactRepository);
        $response = $controller($mockRequest);

        $this->assertEquals(StatusCodeInterface::STATUS_CREATED, $response->getStatusCode());
    }
}
