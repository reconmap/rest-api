<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Users;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\UserRepository;

class GetUserController extends Controller
{

	public function __invoke(ServerRequestInterface $request, array $args): array
	{
		$id = (int)$args['id'];

		$repository = new UserRepository($this->db);
		$user = $repository->findById($id);

		return $user;
	}
}
