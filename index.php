<?php

require 'vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('general');
$logger->pushHandler(new StreamHandler('logs/application.log', Logger::DEBUG));

$templates = new League\Plates\Engine(__DIR__ . '/templates');

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

$request = GuzzleHttp\Psr7\ServerRequest::fromGlobals(); 
$router = new League\Route\Router;
$router->map('GET', '/', 'IndexController::handleRequest');


try {
	$response = $router->dispatch($request);
	echo $response->getBody();
} catch (Exception $e) {
	$controller = new ErrorController;
	$response = $controller->handleRequest($request);
	http_response_code($response->getStatusCode());
	echo $response->getBody();
}

