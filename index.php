<?php declare(strict_types=1);

require 'vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Reconmap\Controllers\ProjectController;

$logger = new Logger('general');
$logger->pushHandler(new StreamHandler('logs/application.log', Logger::DEBUG));

$request = GuzzleHttp\Psr7\ServerRequest::fromGlobals(); 
$router = new League\Route\Router;
$router->map('GET', '/', 'Reconmap\Controllers\IndexController::handleRequest');
$router->map('GET', '/projects', 'Reconmap\Controllers\ProjectsController::handleRequest');
$router->map('GET', '/projects/{id:number}', [ProjectController::class, 'handleRequest']);

try {
	$response = $router->dispatch($request);
	(new \Laminas\HttpHandlerRunner\Emitter\SapiEmitter)->emit($response);
} catch (Exception $e) {
	$controller = new ErrorController;
	$response = $controller->handleRequest($request);
	http_response_code($response->getStatusCode());
	echo $response->getBody();
}

