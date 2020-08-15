<?php

require 'vendor/autoload.php';

use League\Route\Strategy\JsonStrategy;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('general');
$logger->pushHandler(new StreamHandler('logs/application.log', Logger::DEBUG));

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

$request = GuzzleHttp\Psr7\ServerRequest::fromGlobals(); 
$router = new League\Route\Router;
$router->map('GET', '/', 'IndexController::handleRequest');
$router->map('GET', '/projects', 'ProjectsController::handleRequest');


try {
	$response = $router->dispatch($request);
	(new \Laminas\HttpHandlerRunner\Emitter\SapiEmitter)->emit($response);
} catch (Exception $e) {
	$controller = new ErrorController;
	$response = $controller->handleRequest($request);
	http_response_code($response->getStatusCode());
	echo $response->getBody();
}

