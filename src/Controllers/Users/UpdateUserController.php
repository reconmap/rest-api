<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Users;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\UserRepository;

class UpdateUserController extends Controller
{

	public function __invoke(ServerRequestInterface $request, array $args): array
	{
		$id = (int)$args['id'];

		$requestBody = json_decode((string)$request->getBody(), true);
		$column = array_keys($requestBody)[0];
		$value = array_values($requestBody)[0];

		$success = false;
		if (in_array($column, ['timezone'])) {
			$repository = new UserRepository($this->db);
			$success = $repository->updateById($id, $column, $value);
		}

		return ['success' => $success];
	}
}
