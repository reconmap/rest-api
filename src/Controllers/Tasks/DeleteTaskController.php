<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\TaskRepository;

class DeleteTaskController extends Controller
{

	public function __invoke(ServerRequestInterface $request, array $args): array
	{
		$id = $args['id'];

		$userRepository = new TaskRepository($this->db);
		$success = $userRepository->deleteById((int)$id);

		return ['success' => $success];
	}
}
