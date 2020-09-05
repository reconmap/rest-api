<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Users;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditLogAction;
use Reconmap\Repositories\UserRepository;
use Reconmap\Services\AuditLogService;

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
		$auditLogService = new AuditLogService($this->db);
		$auditLogService->insert($loggedInUserId, AuditLogAction::USER_DELETED, ['type' => 'user', 'id' => $userId]);
	}
}
