<?php declare(strict_types=1);

namespace Reconmap\Controllers\Auth;

use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\ConsecutiveParamsTrait;
use Reconmap\Repositories\UserRepository;
use Reconmap\Services\ApplicationConfig;
use Reconmap\Services\AuditLogService;
use Reconmap\Services\RedisServer;
use Symfony\Component\HttpFoundation\Response;

class LoginControllerTest extends TestCase
{
    use ConsecutiveParamsTrait;

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

        $mockAuditLogService = $this->createMock(AuditLogService::class);

        $mockServerRequestInterface = $this->createMock(ServerRequestInterface::class);
        $mockServerRequestInterface->expects($this->never())
            ->method('getBody')
            ->willReturn(Utils::streamFor(json_encode(['username' => 'me', 'password' => 'su123'])));
        $mockServerRequestInterface->expects(($this->exactly(2)))
            ->method('getAttribute')
            ->with(...$this->consecutiveParams(['userId'], ['role']))
            ->willReturnOnConsecutiveCalls(1, 'superuser');

        $mockRedisServer = $this->createMock(RedisServer::class);

        $controller = new LoginController($mockUserRepository, $mockAuditLogService, $mockRedisServer);
        $response = $controller($mockServerRequestInterface, []);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }
}
