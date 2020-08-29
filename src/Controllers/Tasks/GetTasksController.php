<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\TaskRepository;

class GetTasksController extends Controller
{

	public function __invoke(ServerRequestInterface $request, array $args): array
	{
		$repository = new TaskRepository($this->db);
		$tasks = $repository->findAll();

		return $tasks;
	}
}
