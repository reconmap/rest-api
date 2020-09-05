<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditLogAction;
use Reconmap\Repositories\TaskRepository;
use Reconmap\Services\AuditLogService;

class DeleteTaskController extends Controller
{

	public function __invoke(ServerRequestInterface $request, array $args): array
	{
		$taskId = (int)$args['id'];

		$userRepository = new TaskRepository($this->db);
		$success = $userRepository->deleteById($taskId);

		$userId = $request->getAttribute('userId');
		$this->auditAction($userId, $taskId);

		return ['success' => $success];
	}

	private function auditAction(int $loggedInUserId, int $taskId): void
	{
		$auditLogService = new AuditLogService($this->db);
		$auditLogService->insert($loggedInUserId, AuditLogAction::TASK_DELETED, ['type' => 'task', 'id' => $taskId]);
	}
}
