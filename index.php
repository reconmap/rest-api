<?php

declare(strict_types=1);

require 'vendor/autoload.php';

use League\Route\Http\Exception\NotFoundException;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Reconmap\ApiRouter;
use Reconmap\DatabaseFactory;

$logger = new Logger('general');

$container = new League\Container\Container;
$container->delegate(new League\Container\ReflectionContainer);
$container->add(Logger::class, function () use($logger) {
	$logger->pushHandler(new StreamHandler(__DIR__ . '/logs/application.log', Logger::DEBUG));
	return $logger;
});
$container->add(\mysqli::class, DatabaseFactory::createConnection());

$router = new ApiRouter();
$router->mapRoutes($container);

try {
	$request = GuzzleHttp\Psr7\ServerRequest::fromGlobals();
	$response = $router->dispatch($request);
} catch (NotFoundException $e) {
	$logger->error($e->getMessage());
	$response = (new \GuzzleHttp\Psr7\Response)->withStatus(404);
} catch (Exception $e) {
	$logger->error($e->getMessage());
	$response = (new \GuzzleHttp\Psr7\Response)->withStatus(500);
}

(new \Laminas\HttpHandlerRunner\Emitter\SapiEmitter)->emit($response);
