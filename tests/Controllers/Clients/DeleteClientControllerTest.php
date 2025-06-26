<?php declare(strict_types=1);

namespace Reconmap\Controllers\Clients;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Models\AuditActions\AuditActions;
use Reconmap\Repositories\ClientRepository;
use Reconmap\Services\ActivityPublisherService;

class DeleteClientControllerTest extends TestCase
{
    public function testSuccessfulDelete()
    {
        $fakeClientId = 86;

        $mockClientRepository = $this->createMock(ClientRepository::class);
        $mockClientRepository->expects($this->once())
            ->method('deleteById')
            ->with($fakeClientId)
            ->willReturn(true);

        $mockPublisherService = $this->createMock(ActivityPublisherService::class);
        $mockPublisherService->expects($this->once())
            ->method('publish')
            ->with(9, AuditActions::DELETED, 'Client', ['id' => $fakeClientId]);

        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->once())
            ->method('getAttribute')
            ->with('userId')
            ->willReturn(9);

        $args = ['clientId' => $fakeClientId];

        $controller = new DeleteClientController($mockClientRepository, $mockPublisherService);
        $response = $controller($mockRequest, $args);

        $this->assertEquals(204, $response->getStatusCode());
    }
}
