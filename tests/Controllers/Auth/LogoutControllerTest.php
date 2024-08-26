<?php declare(strict_types=1);

namespace Reconmap\Controllers\Auth;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\ConsecutiveParamsTrait;
use Reconmap\Models\AuditActions\UserAuditActions;
use Reconmap\Services\AuditLogService;
use Reconmap\Services\Security\AuthorisationService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LogoutControllerTest extends TestCase
{
    use ConsecutiveParamsTrait;

    public function testLogout()
    {
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->exactly(2))
            ->method('getAttribute')
            ->with(...$this->consecutiveParams(['userId'], ['role']))
            ->willReturnOnConsecutiveCalls(509, 'client');

        $mockAuthorisationService = $this->createMock(AuthorisationService::class);
        $mockAuthorisationService->expects($this->once())
            ->method('isRoleAllowed')
            ->willReturn(true);

        $mockContainer = $this->createMock(ContainerInterface::class);
        $mockContainer->expects($this->once())
            ->method('get')
            ->willReturn($mockAuthorisationService);

        $mockAuditLogService = $this->createMock(AuditLogService::class);
        $mockAuditLogService->expects($this->once())
            ->method('insert')
            ->with(509, UserAuditActions::USER_LOGGED_OUT);

        $controller = new LogoutController($mockAuditLogService);
        $response = $controller($mockRequest, []);

        $this->assertEquals(\Symfony\Component\HttpFoundation\Response::HTTP_OK, $response->getStatusCode());
    }
}
