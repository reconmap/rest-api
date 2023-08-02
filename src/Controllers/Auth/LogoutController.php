<?php declare(strict_types=1);

namespace Reconmap\Controllers\Auth;

use HansOtt\PSR7Cookies\SetCookie;
use Psr\Http\Message\ResponseInterface;
use Reconmap\Controllers\ControllerV2;
use Reconmap\Http\ApplicationRequest;
use Reconmap\Models\AuditActions\UserAuditActions;
use Reconmap\Services\AuditLogService;

class LogoutController extends ControllerV2
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    protected function process(ApplicationRequest $request): ResponseInterface
    {
        $user = $request->getUser();

        $this->auditLogService->insert($user->id, UserAuditActions::USER_LOGGED_OUT);

        $cookie = new SetCookie('reconmap-static','',-1, '/');
        $response = $this->createOkResponse();
        $response = $cookie->addToResponse($response);
        return $response;
    }
}
