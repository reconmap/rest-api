<?php declare(strict_types=1);

namespace Reconmap\Controllers\Users;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditLogAction;
use Reconmap\Services\AuditLogService;

class UsersLogoutController extends Controller
{
    public function __construct(private AuditLogService $auditLogService)
    {
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        $userId = $request->getAttribute('userId');

        $this->auditLogService->insert($userId, AuditLogAction::USER_LOGGED_OUT);

        return ['success' => true];
    }
}
