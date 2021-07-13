<?php declare(strict_types=1);

namespace Reconmap\Controllers\Clients;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Models\AuditLogAction;
use Reconmap\Repositories\ClientRepository;
use Reconmap\Services\ActivityPublisherService;

class UpdateClientControllerTest extends TestCase
{
    public function testHappyPath()
    {
        $fakeClientId = 49;

        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->once())
            ->method('getBody')
            ->willReturn('{"name": "Fancy new Client Name"}');
        $mockRequest->expects($this->once())
            ->method('getAttribute')
            ->with('userId')
            ->willReturn(9);

        $mockClientRepository = $this->createMock(ClientRepository::class);
        $mockClientRepository->expects($this->once())
            ->method('updateById')
            ->with($fakeClientId, ['name' => 'Fancy new Client Name'])
            ->willReturn(true);

        $mockPublisherService = $this->createMock(ActivityPublisherService::class);
        $mockPublisherService->expects($this->once())
            ->method('publish')
            ->with(9, AuditLogAction::CLIENT_MODIFIED, ['type' => 'client', 'id' => $fakeClientId]);

        $args = ['clientId' => $fakeClientId];

        $controller = new UpdateClientController($mockClientRepository, $mockPublisherService);
        $response = $controller($mockRequest, $args);
        $this->assertEquals(['success' => true], $response);
    }
}
