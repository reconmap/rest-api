<?php declare(strict_types=1);

namespace Reconmap\Controllers\Clients;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Models\AuditActions\AuditActions;
use Reconmap\Models\Client;
use Reconmap\Repositories\ClientRepository;
use Reconmap\Services\ActivityPublisherService;
use Reconmap\Services\Filesystem\AttachmentSaver;
use Symfony\Component\HttpFoundation\Response;

class CreateClientControllerTest extends TestCase
{
    public function testHappyPath()
    {
        $expectedClient = new Client();
        $expectedClient->name = 'exciting new client';
        $expectedClient->address = 'evergreen';
        $expectedClient->url = '1.1.1.1';
        $expectedClient->creator_uid = 9;
        $expectedClient->kind = 'client';

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
            ->method('getParsedBody')
            ->willReturn(["name" => "exciting new client", "kind" => "client", "address" => "evergreen", "url" => "1.1.1.1"]);

        $mockActivityPublisherService = $this->createMock(ActivityPublisherService::class);
        $mockActivityPublisherService->expects($this->once())
            ->method('publish')
            ->with(9, AuditActions::CREATED, 'Client', ['id' => 1, 'name' => $expectedClient->name]);

        $mockAttachmentSaver = $this->createMock(AttachmentSaver::class);

        $controller = new CreateClientController($mockProjectRepository, $mockAttachmentSaver, $mockActivityPublisherService);
        $response = $controller($mockRequest);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
    }
}
