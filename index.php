<?php

declare(strict_types=1);

define('RECONMAP_APP_DIR', __DIR__);

require 'vendor/autoload.php';

use League\Container\Container;
use League\Container\ContainerAwareInterface;
use League\Route\Http\Exception\NotFoundException;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Container\ContainerInterface;
use Reconmap\ApiRouter;
use Reconmap\DatabaseFactory;
use Reconmap\Services\Config;
use Reconmap\Services\ConfigConsumer;
use Reconmap\Services\ContainerConsumer;

$logger = new Logger('general');
$logger->pushHandler(new StreamHandler(__DIR__ . '/logs/application.log', Logger::DEBUG));

$config = new Config(__DIR__ . '/config.json');

$container = new League\Container\Container;
$container->delegate(new League\Container\ReflectionContainer);

$container->inflector(ConfigConsumer::class)
	->invokeMethod('setConfig', [Config::class]);
$container->inflector(ContainerConsumer::class)
	->invokeMethod('setContainer', [Container::class]);

$container->add(Logger::class, function () use ($logger) {
	return $logger;
});
$container->add(Config::class, $config);
$container->add(Container::class, $container);
$container->add(\mysqli::class, DatabaseFactory::createConnection());
$container->add(\League\Plates\Engine::class, function () {
	$templates = new \League\Plates\Engine(__DIR__ . '/resources/templates');
	return $templates;
});

$router = new ApiRouter();
$router->mapRoutes($container, $logger);

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
