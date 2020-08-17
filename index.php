<?php

declare(strict_types=1);

require 'vendor/autoload.php';

use Laminas\Diactoros\ResponseFactory;
use League\Route\Http\Exception\NotFoundException;
use League\Route\RouteGroup;
use League\Route\Router;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\AuthMiddleware;
use Reconmap\Controllers\AuditLogController;
use Reconmap\Controllers\DeleteProjectController;
use Reconmap\Controllers\GetProjectsController;
use Reconmap\Controllers\GetProjectTasksController;
use Reconmap\Controllers\GetUsersController;
use Reconmap\Controllers\IndexController;
use Reconmap\Controllers\ProjectController;
use Reconmap\Controllers\ProjectsController;
use Reconmap\Controllers\UsersController;
use Reconmap\Controllers\UsersLoginController;

$request = GuzzleHttp\Psr7\ServerRequest::fromGlobals();

$container = new League\Container\Container;
$container->delegate(new League\Container\ReflectionContainer);
$container->add(Logger::class, function () {
	$logger = new Logger('general');
	$logger->pushHandler(new StreamHandler(__DIR__ . '/logs/application.log', Logger::DEBUG));
	return $logger;
});
$container->add(\mysqli::class, function () {
	// @todo pull credentials from env variables
	$db = new \mysqli('db', 'reconmapper', 'reconmapped', 'reconmap');
	return $db;
});

$authMiddleware = new AuthMiddleware;

$responseFactory = new ResponseFactory;
$strategy = new League\Route\Strategy\JsonStrategy($responseFactory);
$strategy->setContainer($container);

$router = new League\Route\Router;
$router->setStrategy($strategy);

// OPTIONS to support CORS
$router->map('OPTIONS', '/{any:.*}', function (ServerRequestInterface $request): ResponseInterface {
	$response = (new \GuzzleHttp\Psr7\Response)->withStatus(200);
	return $response
		->withHeader('Access-Control-Allow-Methods', 'GET,POST,PUT')
		->withHeader('Access-Control-Allow-Headers', 'Authorization')
		->withHeader('Access-Control-Allow-Origin', '*');
});

$router->map('GET', '/', [IndexController::class, 'handleRequest']);
$router->map('POST', '/users/login', UsersLoginController::class);
$router->group('', function (RouteGroup $router): void {
	$router->map('GET', '/users', GetUsersController::class);
	$router->map('GET', '/auditlog', [AuditLogController::class, 'handleRequest']);
	$router->map('GET', '/projects', GetProjectsController::class);
	$router->map('GET', '/projects/{id:number}', [ProjectController::class, 'handleRequest']);
	$router->map('GET', '/projects/{id:number}/tasks', GetProjectTasksController::class);
	$router->map('DELETE', '/projects/{id:number}', DeleteProjectController::class);
})->middleware($authMiddleware);

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
