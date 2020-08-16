<?php declare(strict_types=1);

require 'vendor/autoload.php';

use Laminas\Diactoros\ResponseFactory;
use League\Route\Http\Exception\NotFoundException;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\ProjectController;

$logger = new Logger('general');
$logger->pushHandler(new StreamHandler('logs/application.log', Logger::DEBUG));

$request = GuzzleHttp\Psr7\ServerRequest::fromGlobals(); 

$responseFactory = new ResponseFactory;
$strategy = new League\Route\Strategy\JsonStrategy($responseFactory);

$router = new League\Route\Router;
$router->setStrategy($strategy);

// OPTIONS to support CORS
$router->map('OPTIONS', '/{any:.*}', function (ServerRequestInterface $request) : ResponseInterface {
	$response = (new \GuzzleHttp\Psr7\Response)->withStatus(200);
	$response->getBody()->write('hoo');
	return $response->withHeader('Access-Control-Allow-Methods', 'GET,POST,PUT')
		->withHeader('Access-Control-Allow-Headers', 'Authorization')
		->withHeader('Access-Control-Allow-Origin', '*');
});

$router->map('GET', '/', 'Reconmap\Controllers\IndexController::handleRequest');
$router->map('GET', '/projects', 'Reconmap\Controllers\ProjectsController::handleRequest');
$router->map('GET', '/projects/{id:number}', [ProjectController::class, 'handleRequest']);
	
try {
	$response = $router->dispatch($request);
} catch (NotFoundException $e) {
	$logger->error($e->getMessage());
	$response = (new \GuzzleHttp\Psr7\Response)->withStatus(404);
} catch (Exception $e) {
	$logger->error($e->getMessage());
	$response = (new \GuzzleHttp\Psr7\Response)->withStatus(500);
}

(new \Laminas\HttpHandlerRunner\Emitter\SapiEmitter)->emit($response);

