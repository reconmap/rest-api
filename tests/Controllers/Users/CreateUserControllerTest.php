<?php declare(strict_types=1);

namespace Reconmap\Controllers\Users;

use GuzzleHttp\Psr7\Utils;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Repositories\UserRepository;
use Reconmap\Services\AuditLogService;
use Reconmap\Services\EmailService;
use Reconmap\Services\KeycloakService;
use Reconmap\Services\PasswordGenerator;

class CreateUserControllerTest extends TestCase
{
    public function testAutoPasswordGeneration()
    {
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->once())
            ->method('getBody')
            ->willReturn(Utils::streamFor('{"email": "foo@bar.com", "full_name": "Foo Bar", "unencryptedPassword": null, "sendEmailToUser": true}'));
        $mockRequest->expects($this->once())
            ->method('getAttribute')
            ->with('userId')
            ->willReturn(9);

        $mockUserRepository = $this->createMock(UserRepository::class);

        $mockPasswordGenerator = $this->createMock(PasswordGenerator::class);
        $mockPasswordGenerator->expects($this->once())
            ->method('generate')
            ->with(24)
            ->willReturn('123456789012345678901234');

        $mockEmailService = $this->createMock(EmailService::class);

        $mockAuditLogService = $this->createMock(AuditLogService::class);
        $mockAuditLogService->expects($this->once())
            ->method('insert')
            ->with(9, 'Created user');

        $mockLogger = $this->createMock(Logger::class);

        $mockKeycloakService = $this->createMock(KeycloakService::class);

        $controller = new CreateUserController($mockKeycloakService, $mockUserRepository, $mockPasswordGenerator, $mockEmailService, $mockAuditLogService);
        $controller->setLogger($mockLogger);

        $user = $controller($mockRequest);

        $this->assertNotFalse($user['id']);
        $this->assertEquals(1, $user['active']);
        $this->assertEquals('Foo Bar', $user['full_name']);
        $this->assertEquals('foo@bar.com', $user['email']);
    }

    public function testManualPasswordGeneration()
    {
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->once())
            ->method('getBody')
            ->willReturn(Utils::streamFor('{"email": "foo@bar.com", "full_name": "Foo Bar", "unencryptedPassword": "mysecreto", "sendEmailToUser": true}'));
        $mockRequest->expects($this->once())
            ->method('getAttribute')
            ->with('userId')
            ->willReturn(9);

        $mockUserRepository = $this->createMock(UserRepository::class);

        $mockPasswordGenerator = $this->createMock(PasswordGenerator::class);
        $mockPasswordGenerator->expects($this->never())
            ->method('generate');

        $mockEmailService = $this->createMock(EmailService::class);

        $mockAuditLogService = $this->createMock(AuditLogService::class);
        $mockAuditLogService->expects($this->once())
            ->method('insert')
            ->with(9, 'Created user');

        $mockLogger = $this->createMock(Logger::class);

        $mockKeycloakService = $this->createMock(KeycloakService::class);

        $controller = new CreateUserController($mockKeycloakService, $mockUserRepository, $mockPasswordGenerator, $mockEmailService, $mockAuditLogService);
        $controller->setLogger($mockLogger);

        $user = $controller($mockRequest);

        $this->assertNotFalse($user['id']);
        $this->assertEquals(1, $user['active']);
        $this->assertEquals('Foo Bar', $user['full_name']);
        $this->assertEquals('foo@bar.com', $user['email']);
    }
}
