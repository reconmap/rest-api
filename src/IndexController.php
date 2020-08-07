<?php
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class IndexController extends Controller {

	public function handleRequest(ServerRequestInterface $request) : ResponseInterface {
		$body = $this->templates->render('index', ['name' => 'user']);

		$response = new GuzzleHttp\Psr7\Response;
		$response->getBody()->write($body);
		return $response;
	}
}

