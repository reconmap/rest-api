<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Users;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\UserRepository;

class GetUserController extends Controller
{

	public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
	{
		$id = (int)$args['id'];

		$repository = new UserRepository($this->db);
		$user = $repository->findById($id);

		$response = new \GuzzleHttp\Psr7\Response;
		$response->getBody()->write(json_encode($user));
		return $response->withHeader('Access-Control-Allow-Origin', '*');
	}
}
