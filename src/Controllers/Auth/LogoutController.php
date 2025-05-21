<?php declare(strict_types=1);

namespace Reconmap\Controllers\Auth;

use GuzzleHttp\Psr7\HttpFactory;
use Psr\Http\Message\ResponseInterface;
use Reconmap\Controllers\ControllerV2;
use Reconmap\Http\ApplicationRequest;
use Reconmap\Models\AuditActions\UserAuditActions;
use Reconmap\Services\AuditLogService;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class LogoutController extends ControllerV2
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    protected function process(ApplicationRequest $request): ResponseInterface
    {
        $user = $request->getUser();

        $this->auditLogService->insert($user->id, UserAuditActions::LOGGED_OUT, 'User');

        $staticCookie = new Cookie('reconmap-static', '', -1, '/');

        $response = new Response();
        $response->headers->setCookie($staticCookie);

        $psr17Factory = new HttpFactory();
        $psrHttpFactory = new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

        return $psrHttpFactory->createResponse($response);
    }
}
