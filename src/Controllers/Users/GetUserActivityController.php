<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Users;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\AuditLogRepository;

class GetUserActivityController extends Controller
{

	public function __invoke(ServerRequestInterface $request, array $args): array
	{
		$id = (int)$args['id'];

		$repository = new AuditLogRepository($this->db);
		$results = $repository->findByUserId($id);

		return $results;
	}
}
