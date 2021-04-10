<?php declare(strict_types=1);

namespace Reconmap\Controllers\Users;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Repositories\UserRepository;
use Reconmap\Services\ApplicationConfig;
use Reconmap\Services\AuditLogService;
use Reconmap\Services\PasswordGenerator;
use Reconmap\Services\RedisServer;
use Reconmap\Services\TemplateEngine;

class CreateUserControllerTest extends TestCase
{
    public function testNoEmail()
    {
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->once())
            ->method('getBody')
            ->willReturn('{"email":"foo@bar.com", "full_name": "Foo Bar", "unencryptedPassword":"123"}');
        $mockRequest->expects($this->once())
            ->method('getAttribute')
            ->with('userId')
            ->willReturn(9);

        $mockUserRepository = $this->createMock(UserRepository::class);
        $mockRedisServer = $this->createMock(RedisServer::class);
        $mockPasswordGenerator = $this->createMock(PasswordGenerator::class);
        $mockPasswordGenerator->expects($this->once())
            ->method('generate')
            ->with(24)
            ->willReturn('123456789012345678901234');

        $mockAppConfig = $this->createMock(ApplicationConfig::class);
        $mockAppConfig->expects($this->once())
            ->method('getSettings')
            ->with('cors')
            ->willReturn(['allowedOrigins' => ['reconmap.local']]);

        $mockTemplateEngine = $this->createMock(TemplateEngine::class);
        $mockTemplateEngine->expects($this->once())
            ->method('render')
            ->willReturn('xxx');

        $mockAuditLogService = $this->createMock(AuditLogService::class);
        $mockAuditLogService->expects($this->once())
            ->method('insert')
            ->with(9, 'Created user');

        $controller = new CreateUserController($mockUserRepository, $mockRedisServer, $mockPasswordGenerator, $mockAppConfig, $mockAuditLogService);
        $controller->setTemplate($mockTemplateEngine);
        $user = $controller($mockRequest);

        $this->assertNotFalse($user['id']);
        $this->assertEquals(1, $user['active']);
        $this->assertEquals('Foo Bar', $user['full_name']);
        $this->assertEquals('foo@bar.com', $user['email']);
    }
}
