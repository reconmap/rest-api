<?php

declare(strict_types=1);

namespace Reconmap\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Repositories\UserRepository;

class DeleteUserController extends Controller
{

	public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
	{
		$id = $args['id'];

		$userRepository = new UserRepository($this->db);
		$success = $userRepository->deleteById((int)$id);

		$response = new \GuzzleHttp\Psr7\Response;
		$response->getBody()->write(json_encode($success));
		return $response->withHeader('Access-Control-Allow-Origin', '*');
	}
}
