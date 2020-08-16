<?php declare(strict_types=1);

namespace Reconmap\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuditLogController extends Controller {

	public function handleRequest(ServerRequestInterface $request) : ResponseInterface {
		$sql = <<<SQL
		SELECT al.*, u.*
		FROM audit_log al
		INNER JOIN user u ON (u.id = al.user_id)
		ORDER BY al.insert_ts DESC
		SQL;
		
		$rs = $this->db->query($sql);
		$auditLog = $rs->fetch_all(MYSQLI_ASSOC);

		$this->validateJwtToken($request);

		$response = new \GuzzleHttp\Psr7\Response;
		$response->getBody()->write(json_encode($auditLog));
		return $response->withHeader('Access-Control-Allow-Origin', '*')
				  ->withAddedHeader('content-type', 'application/json');
	}
}
