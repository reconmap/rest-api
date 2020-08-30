<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Users;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\UserRepository;

class DeleteUserController extends Controller
{

	public function __invoke(ServerRequestInterface $request, array $args): array
	{
		$id = $args['id'];

		$userRepository = new UserRepository($this->db);
		$success = $userRepository->deleteById((int)$id);

		return ['success' => $success];
	}
}
