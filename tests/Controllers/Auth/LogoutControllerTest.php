<?php declare(strict_types=1);

namespace Reconmap\Controllers\Auth;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Models\AuditActions\AuditLogAction;
use Reconmap\Services\AuditLogService;

class LogoutControllerTest extends TestCase
{
    public function testLogout()
    {
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->once())
            ->method('getAttribute')
            ->with('userId')
            ->willReturn(509);

        $mockAuditLogService = $this->createMock(AuditLogService::class);
        $mockAuditLogService->expects($this->once())
            ->method('insert')
            ->with(509, AuditLogAction::USER_LOGGED_OUT);

        $controller = new LogoutController($mockAuditLogService);
        $response = $controller($mockRequest);

        $this->assertTrue($response['success']);
    }
}
