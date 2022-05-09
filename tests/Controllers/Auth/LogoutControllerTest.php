<?php declare(strict_types=1);

namespace Reconmap\Controllers\Auth;

use Fig\Http\Message\StatusCodeInterface;
use League\Container\Container;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Models\AuditActions\UserAuditActions;
use Reconmap\Services\AuditLogService;
use Reconmap\Services\Security\AuthorisationService;

class LogoutControllerTest extends TestCase
{
    public function testLogout()
    {
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->exactly(2))
            ->method('getAttribute')
            ->withConsecutive(['userId'], ['role'])
            ->willReturnOnConsecutiveCalls(509, 'client');

        $mockAuthorisationService = $this->createMock(AuthorisationService::class);
        $mockAuthorisationService->expects($this->once())
            ->method('isRoleAllowed')
            ->willReturn(true);

        $mockContainer = $this->createMock(Container::class);
        $mockContainer->expects($this->once())
            ->method('get')
            ->willReturn($mockAuthorisationService);

        $mockAuditLogService = $this->createMock(AuditLogService::class);
        $mockAuditLogService->expects($this->once())
            ->method('insert')
            ->with(509, UserAuditActions::USER_LOGGED_OUT);

        $controller = new LogoutController($mockAuditLogService);
        $controller->setContainer($mockContainer);
        $response = $controller($mockRequest);

        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
    }
}
