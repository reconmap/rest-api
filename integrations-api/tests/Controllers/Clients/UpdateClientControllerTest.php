<?php declare(strict_types=1);

namespace Reconmap\Controllers\Clients;

use GuzzleHttp\Psr7\Utils;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\ConsecutiveParamsTrait;
use Reconmap\Models\AuditActions\AuditActions;
use Reconmap\Repositories\ClientRepository;
use Reconmap\Services\ActivityPublisherService;
use Reconmap\Services\Security\AuthorisationService;
use Symfony\Component\HttpFoundation\Response;

class UpdateClientControllerTest extends TestCase
{
    use ConsecutiveParamsTrait;

    public function testHappyPath()
    {
        $fakeClientId = 49;

        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->once())
            ->method('getBody')
            ->willReturn(Utils::streamFor('{"name": "Fancy new Client Name"}'));
        $mockRequest->expects($this->exactly(2))
            ->method('getAttribute')
            ->with(...$this->consecutiveParams(['role'], ['userId']))
            ->willReturnOnConsecutiveCalls('superuser', 9);

        $mockClientRepository = $this->createMock(ClientRepository::class);
        $mockClientRepository->expects($this->once())
            ->method('updateById')
            ->with($fakeClientId, ['name' => 'Fancy new Client Name'])
            ->willReturn(true);

        $mockAuthorisationService = $this->createMock(AuthorisationService::class);
        $mockAuthorisationService->expects($this->once())
            ->method('isRoleAllowed')
            ->willReturn(true);

        $mockPublisherService = $this->createMock(ActivityPublisherService::class);
        $mockPublisherService->expects($this->once())
            ->method('publish')
            ->with(9, AuditActions::UPDATED, 'client', ['id' => $fakeClientId]);

        $args = ['clientId' => $fakeClientId];

        $controller = new UpdateClientController($mockAuthorisationService, $mockPublisherService, $mockClientRepository);
        $controller->setLogger($this->createMock(Logger::class));
        $response = $controller($mockRequest, $args);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }
}
