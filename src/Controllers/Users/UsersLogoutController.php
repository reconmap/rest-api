<?php declare(strict_types=1);

namespace Reconmap\Controllers\Users;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditLogAction;
use Reconmap\Services\AuditLogService;

class UsersLogoutController extends Controller
{

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $userId = $request->getAttribute('userId');

        $auditLogService = new AuditLogService($this->db);
        $auditLogService->insert($userId, AuditLogAction::USER_LOGGED_OUT);

        $response = new Response;
        return $response
            ->withStatus(403)
            ->withHeader('Access-Control-Allow-Methods', 'GET,POST,PUT')
            ->withHeader('Access-Control-Allow-Origin', '*');
    }
}
