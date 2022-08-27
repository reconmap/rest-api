<?php declare(strict_types=1);

namespace Reconmap\Controllers\Auth;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Reconmap\Controllers\ControllerV2;
use Reconmap\Http\ApplicationRequest;
use Reconmap\Models\AuditActions\UserAuditActions;
use Reconmap\Repositories\UserRepository;
use Reconmap\Services\AuditLogService;
use Reconmap\Services\Security\Permissions;

class LoginController extends ControllerV2
{
    public function __construct(
        private readonly UserRepository  $userRepository,
        private readonly AuditLogService $auditLogService)
    {
    }

    protected function process(ApplicationRequest $request): ResponseInterface
    {
        $requestUser = $request->getUser();

        $this->audit($requestUser->id);

        $user = $this->userRepository->findById($requestUser->id);

        $user['permissions'] = Permissions::ByRoles[$requestUser->role];

        $response = new Response;
        $response->getBody()->write(json_encode($user));

        return $response->withHeader('Content-type', 'application/json');
    }

    private function audit(?int $userId): void
    {
        $this->auditLogService->insert($userId, UserAuditActions::USER_LOGGED_IN, null);
    }
}
