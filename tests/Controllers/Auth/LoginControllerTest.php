<?php declare(strict_types=1);

namespace Reconmap\Controllers\Auth;

use Fig\Http\Message\StatusCodeInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Repositories\UserRepository;
use Reconmap\Services\ApplicationConfig;
use Reconmap\Services\AuditLogService;
use Reconmap\Services\JwtPayloadCreator;

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
            ->method('findByUsername')
            ->with('me')
            ->willReturn($fakeUser);

        $mockApplicationConfig = $this->createMock(ApplicationConfig::class);
        $mockApplicationConfig->expects($this->once())
            ->method('getSettings')
            ->with('jwt')
            ->willReturn(['key' => 'aaa']);

        $mockAuditLogService = $this->createMock(AuditLogService::class);
        $mockJwtPayloadCreator = $this->createMock(JwtPayloadCreator::class);

        $mockServerRequestInterface = $this->createMock(ServerRequestInterface::class);
        $mockServerRequestInterface->expects($this->once())
            ->method('getBody')
            ->willReturn(json_encode(['username' => 'me', 'password' => 'su123']));

        $controller = new LoginController($mockUserRepository, $mockApplicationConfig, $mockAuditLogService, $mockJwtPayloadCreator);
        $response = $controller($mockServerRequestInterface);

        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
    }
}
