<?php

declare(strict_types=1);

namespace Reconmap\Controllers\AuditLog;

use Laminas\Diactoros\CallbackStream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditLogAction;
use Reconmap\Repositories\AuditLogRepository;
use Reconmap\Services\AuditLogService;

class ExportAuditLogController extends Controller
{

	public function __invoke(ServerRequestInterface $request): ResponseInterface
	{
		$repository = new AuditLogRepository($this->db);
		$auditLog = $repository->findAll();

		$fileName = 'reconmap-auditlog-' . date('Ymd-His') . '.csv';

		$response = new \GuzzleHttp\Psr7\Response;

		$body = new CallbackStream(function () use ($auditLog) {
			$f = fopen('php://output', 'w');
			foreach ($auditLog as $log) {
				fputcsv($f, $log);
			}
		});

		$userId = $request->getAttribute('userId');
		$this->auditAction($userId);

		return $response
			->withBody($body)
			->withHeader('Access-Control-Expose-Headers', 'Content-Disposition')
			->withHeader('Content-Disposition', 'attachment; filename="' . $fileName . '";')
			->withAddedHeader('Content-Type', 'application/csv; charset=UTF-8');
	}

	private function auditAction(int $loggedInUserId): void
	{
		$auditLogService = new AuditLogService($this->db);
		$auditLogService->insert($loggedInUserId, AuditLogAction::AUDIT_LOG_EXPORTED);
	}
}
