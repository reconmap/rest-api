<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Users;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditLogAction;
use Reconmap\Repositories\AuditLogRepository;
use Reconmap\Services\NetworkService;

class UsersLogoutController extends Controller
{

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $userId = $request->getAttribute('userId');

        $clientIp = (new NetworkService)->getClientIp();
        $auditRepository = new AuditLogRepository($this->db);
        $auditRepository->insert($userId, $clientIp, AuditLogAction::USER_LOGGED_OUT);

        $response = new \GuzzleHttp\Psr7\Response;

        return $response
            ->withStatus(403)
            ->withHeader('Access-Control-Allow-Methods', 'GET,POST,PUT')
            ->withHeader('Access-Control-Allow-Origin', '*');
    }
}
