<?php

declare(strict_types=1);

define('RECONMAP_APP_DIR', realpath('../'));

require RECONMAP_APP_DIR . '/vendor/autoload.php';

use GuzzleHttp\Psr7\Response;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use League\Container\Container;
use League\Route\Http\Exception\NotFoundException;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Reconmap\ApiRouter;
use Reconmap\DatabaseFactory;
use Reconmap\Services\Config;
use Reconmap\Services\ConfigConsumer;
use Reconmap\Services\ConfigLoader;
use Reconmap\Services\ContainerConsumer;

$logger = new Logger('http');
$logger->pushHandler(new StreamHandler(RECONMAP_APP_DIR . '/logs/application.log', Logger::DEBUG));

$config = (new ConfigLoader())->loadFromFile(RECONMAP_APP_DIR . '/config.json');
$config->update('appDir', RECONMAP_APP_DIR);

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
$container->add(\mysqli::class, DatabaseFactory::createConnection($config));
$container->add(\Redis::class, function () {
    $redis = new Redis();
    if (false === $redis->connect('redis', 6379)) {
        throw new Exception('Unable to connect to Redis');
    }
    if (false === $redis->auth(['default', 'REconDIS'])) {
        throw new Exception('Unable to authenticate to Redis');
    }
    return $redis;
});

$router = new ApiRouter();
$router->mapRoutes($container, $logger);

try {
    $request = GuzzleHttp\Psr7\ServerRequest::fromGlobals();
    $response = $router->dispatch($request);
} catch (NotFoundException $e) {
    $logger->error($e->getMessage());
    $response = (new Response)->withStatus(404);
} catch (Exception $e) {
    $logger->error($e->getMessage());
    $response = (new Response)->withStatus(500);
}

(new SapiEmitter)->emit($response);
