<?php

declare(strict_types=1);

define('RECONMAP_APP_DIR', dirname(__DIR__, 2));

require RECONMAP_APP_DIR . '/vendor/autoload.php';

use League\Container\Container;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Reconmap\DatabaseFactory;
use Reconmap\QueueProcessor;
use Reconmap\Services\Config;
use Reconmap\Services\ConfigConsumer;
use Reconmap\Services\ConfigLoader;
use Reconmap\Services\ContainerConsumer;
use Reconmap\Tasks\TaskResultProcessor;

$logger = new Logger('cron');
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

$tasksProcessor = new TaskResultProcessor($config, $logger, $container->get(\mysqli::class), $container->get(\Redis::class));

/** @var QueueProcessor $queueProcessor */
$queueProcessor = $container->get(QueueProcessor::class);
$exitCode = $queueProcessor->run($tasksProcessor, 'tasks:queue');

exit($exitCode);

