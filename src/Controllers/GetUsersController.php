<?php

declare(strict_types=1);

namespace Reconmap\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Repositories\UserRepository;

class GetUsersController extends Controller
{

	public function __invoke(ServerRequestInterface $request): ResponseInterface
	{
		$userRepository = new UserRepository($this->db);
		$users = $userRepository->findAll();

		$response = new \GuzzleHttp\Psr7\Response;
		$response->getBody()->write(json_encode($users));
		return $response->withHeader('Access-Control-Allow-Origin', '*')
			->withAddedHeader('content-type', 'application/json');
	}
}
