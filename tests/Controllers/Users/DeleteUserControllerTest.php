<?php declare(strict_types=1);

namespace Reconmap\Controllers\Users;

use Fig\Http\Message\StatusCodeInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Repositories\UserRepository;
use Reconmap\Services\ActivityPublisherService;
use Reconmap\Services\KeycloakService;

class DeleteUserControllerTest extends TestCase
{
    public function testSuccessfulDelete()
    {
        $fakeUserId = 86;

        $mockUserRepository = $this->createMock(UserRepository::class);
        $mockUserRepository->expects($this->once())
            ->method('findById')
            ->with($fakeUserId)
            ->willReturn(['subject_id' => 'xxx-yyy']);
        $mockUserRepository->expects($this->once())
            ->method('deleteById')
            ->with($fakeUserId)
            ->willReturn(true);

        $mockKeycloakService = $this->createMock(KeycloakService::class);

        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->once())
            ->method('getAttribute')
            ->willReturn(1);

        $mockActivityPublisher = $this->createMock(ActivityPublisherService::class);

        $args = ['userId' => $fakeUserId];

        $controller = new DeleteUserController($mockUserRepository, $mockKeycloakService, $mockActivityPublisher);
        $response = $controller($mockRequest, $args);

        $this->assertEquals(StatusCodeInterface::STATUS_NO_CONTENT, $response->getStatusCode());
    }
}
