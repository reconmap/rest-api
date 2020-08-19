<?php

declare(strict_types=1);

namespace Reconmap\Controllers\AuditLog;

use Laminas\Diactoros\CallbackStream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\AuditLogRepository;

class ExportAuditLogController extends Controller
{

	public function __invoke(ServerRequestInterface $request): ResponseInterface
	{
		$repository = new AuditLogRepository($this->db);
		$auditLog = $repository->findAll();

		$fileName = 'reconmap-auditlog-' . date('Y-m-d') . '.csv';		

		$response = new \GuzzleHttp\Psr7\Response;
		
		$body = new CallbackStream(function() use($auditLog) {
			$f = fopen('php://output', 'w');
			foreach($auditLog as $log) {
				fputcsv($f, $log);
			}
		});

		return $response
			->withBody($body)
			->withHeader('Access-Control-Expose-Headers', 'Content-Disposition')
			->withHeader('Access-Control-Allow-Origin', '*')
			->withHeader('Content-Disposition', 'attachment; filename="' . $fileName . '";')
			->withAddedHeader('Content-Type', 'application/csv; charset=UTF-8');
	}
}
