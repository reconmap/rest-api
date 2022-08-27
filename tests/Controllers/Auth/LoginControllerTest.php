<?php declare(strict_types=1);

namespace Reconmap\Controllers\Auth;

use Fig\Http\Message\StatusCodeInterface;
use League\Container\Container;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Repositories\UserRepository;
use Reconmap\Services\ApplicationConfig;
use Reconmap\Services\AuditLogService;
use Reconmap\Services\JwtPayloadCreator;
use Reconmap\Services\Security\AuthorisationService;

class LoginControllerTest extends TestCase
{
    public function testLogin()
    {
        $fakeUser = ['id' => 56,
            'password' => '$2y$10$7u3qUhud4prBZdFVmODvXOCBuQBgq6MYHvZT7N74cMG/mnVBwiu7W',
            'mfa_enabled' => false,
            'role' => 'superuser'];

        $mockUserRepository = $this->createMock(UserRepository::class);
        $mockUserRepository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($fakeUser);

        $mockApplicationConfig = $this->createMock(ApplicationConfig::class);
        $mockApplicationConfig->expects($this->never())
            ->method('getSettings')
            ->with('jwt')
            ->willReturn(['key' => 'aaa']);

        $mockAuthorisationService = $this->createMock(AuthorisationService::class);
        $mockAuthorisationService->expects($this->once())
            ->method('isRoleAllowed')
            ->willReturn(true);

        $mockContainer = $this->createMock(Container::class);
        $mockContainer->expects($this->once())
            ->method('get')
            ->willReturn($mockAuthorisationService);

        $mockAuditLogService = $this->createMock(AuditLogService::class);
        $mockJwtPayloadCreator = $this->createMock(JwtPayloadCreator::class);

        $mockServerRequestInterface = $this->createMock(ServerRequestInterface::class);
        $mockServerRequestInterface->expects($this->never())
            ->method('getBody')
            ->willReturn(json_encode(['username' => 'me', 'password' => 'su123']));
        $mockServerRequestInterface->expects(($this->exactly(2)))
            ->method('getAttribute')
            ->withConsecutive(['userId'], ['role'])
            ->willReturnOnConsecutiveCalls(1, 'superuser');

        $controller = new LoginController($mockUserRepository, $mockAuditLogService);
        $controller->setContainer($mockContainer);
        $response = $controller($mockServerRequestInterface);

        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
    }
}
