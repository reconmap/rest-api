<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Users;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditLogAction;
use Reconmap\Repositories\AuditLogRepository;
use Reconmap\Repositories\UserRepository;
use Reconmap\Services\NetworkService;

class DeleteUserController extends Controller
{

	public function __invoke(ServerRequestInterface $request, array $args): array
	{
		$userId = (int)$args['id'];
		$loggedInUserId = $request->getAttribute('userId');

		$userRepository = new UserRepository($this->db);
		$success = $userRepository->deleteById($userId);

		$this->auditAction($loggedInUserId, $userId);

		return ['success' => $success];
	}

	private function auditAction(int $loggedInUserId, int $userId): void
	{
		$clientIp = (new NetworkService)->getClientIp();
		$auditRepository = new AuditLogRepository($this->db);
		$auditRepository->insert($loggedInUserId, $clientIp, AuditLogAction::USER_DELETED . " (user id: $userId)");
	}
}
