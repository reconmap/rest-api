<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Targets;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\TargetRepository;

class CreateTargetController extends Controller
{

	public function __invoke(ServerRequestInterface $request, array $args): array
	{
		$projectId = (int)$args['id'];
		$requestBody = json_decode((string)$request->getBody());

		$target = $requestBody;

		$repository = new TargetRepository($this->db);
		$result = $repository->insert($projectId, $target->name, $target->kind);

		return ['success' => $result];
	}
}
