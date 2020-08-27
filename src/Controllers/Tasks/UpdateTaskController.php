<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\TaskRepository;

class UpdateTaskController extends Controller
{

	public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
	{
		$id = (int)$args['id'];

		$requestBody = json_decode((string)$request->getBody(), true);
		$column = array_keys($requestBody)[0];
		$value = array_values($requestBody)[0];

		$success = false;
		if (in_array($column, ['completed'])) {
			$repository = new TaskRepository($this->db);
			$success = $repository->updateById($id, $column, $value);
		}


		$response = new \GuzzleHttp\Psr7\Response;
		$response->getBody()->write(json_encode($success));
		return $response->withHeader('Access-Control-Allow-Origin', '*');
	}
}
