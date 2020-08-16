<?php declare(strict_types=1);

require 'vendor/autoload.php';

use Laminas\Diactoros\ResponseFactory;
use League\Route\Http\Exception\NotFoundException;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\AuditLogController;
use Reconmap\Controllers\IndexController;
use Reconmap\Controllers\ProjectController;
use Reconmap\Controllers\UsersController;

$request = GuzzleHttp\Psr7\ServerRequest::fromGlobals(); 

$container = new League\Container\Container;
$container->delegate(new League\Container\ReflectionContainer);
$container->add(Logger::class, function() {
	$logger = new Logger('general');
	$logger->pushHandler(new StreamHandler('logs/application.log', Logger::DEBUG));
	return $logger;
});
$container->add(\mysqli::class, function() {
	// @todo pull credentials from env variables
	$db = new \mysqli('db', 'reconmapper', 'reconmapped', 'reconmap');
	return $db;
});

$responseFactory = new ResponseFactory;
$strategy = new League\Route\Strategy\JsonStrategy($responseFactory);
$strategy->setContainer($container);

$router = new League\Route\Router;
$router->setStrategy($strategy);

// OPTIONS to support CORS
$router->map('OPTIONS', '/{any:.*}', function (ServerRequestInterface $request) : ResponseInterface {
	$response = (new \GuzzleHttp\Psr7\Response)->withStatus(200);
	return $response
		->withHeader('Access-Control-Allow-Methods', 'GET,POST,PUT')
		->withHeader('Access-Control-Allow-Headers', 'Authorization')
		->withHeader('Access-Control-Allow-Origin', '*');
});

$router->map('GET', '/', [IndexController::class, 'handleRequest']);
$router->map('POST', '/users', [UsersController::class, 'handleRequest']);
$router->map('GET', '/auditlog', [AuditLogController::class, 'handleRequest']);
$router->map('GET', '/projects', [ProjectsController::class, 'handleRequest']);
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

