<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\TargetRepository;

class GetProjectTargetsController extends Controller
{

	public function __invoke(ServerRequestInterface $request, array $args): array
	{
		$id = (int)$args['id'];

		$repository = new TargetRepository($this->db);
		$targets = $repository->findByProjectId($id);

		return $targets;
	}
}
