<?php declare(strict_types=1);

require 'vendor/autoload.php';

use League\Route\Http\Exception\NotFoundException;
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
} catch (NotFoundException $e) {
	$logger->error($e->getMessage());
	$response = (new \GuzzleHttp\Psr7\Response)->withStatus(404);
} catch (Exception $e) {
	$logger->error($e->getMessage());
	$response = (new \GuzzleHttp\Psr7\Response)->withStatus(500);
}

(new \Laminas\HttpHandlerRunner\Emitter\SapiEmitter)->emit($response);

