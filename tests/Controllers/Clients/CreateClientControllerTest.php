<?php declare(strict_types=1);

namespace Reconmap\Controllers\Clients;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Models\AuditActions\ClientAuditActions;
use Reconmap\Models\Client;
use Reconmap\Repositories\ClientRepository;
use Reconmap\Services\ActivityPublisherService;

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

        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->once())
            ->method('getAttribute')
            ->with('userId')
            ->willReturn(9);
        $mockRequest->expects($this->once())
            ->method('getBody')
            ->willReturn('{"name":"exciting new client","address":"evergreen","url":"1.1.1.1"}');

        $mockActivityPublisherService = $this->createMock(ActivityPublisherService::class);
        $mockActivityPublisherService->expects($this->once())
            ->method('publish')
            ->with(9, ClientAuditActions::CREATED, ['type' => 'client', 'id' => 1, 'name' => $expectedClient->name]);

        $controller = new CreateClientController($mockProjectRepository, $mockActivityPublisherService);
        $response = $controller($mockRequest);

        $this->assertEquals(\Symfony\Component\HttpFoundation\Response::HTTP_CREATED, $response->getStatusCode());
    }
}
