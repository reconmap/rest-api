<?php

declare(strict_types=1);

define('RECONMAP_APP_DIR', dirname(__DIR__, 2));

require RECONMAP_APP_DIR . '/vendor/autoload.php';

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Reconmap\QueueProcessor;
use Reconmap\Services\ApplicationContainer;
use Reconmap\Services\ConfigLoader;
use Reconmap\Tasks\TaskResultProcessor;

$logger = new Logger('cron');
$logger->pushHandler(new StreamHandler(RECONMAP_APP_DIR . '/logs/application.log', Logger::DEBUG));

$config = (new ConfigLoader())->loadFromFile(RECONMAP_APP_DIR . '/config.json');
$config->update('appDir', RECONMAP_APP_DIR);

$container = new ApplicationContainer($config, $logger);

$tasksProcessor = new TaskResultProcessor($config, $logger, $container->get(\mysqli::class), $container->get(\Redis::class));

/** @var QueueProcessor $queueProcessor */
$queueProcessor = $container->get(QueueProcessor::class);
$exitCode = $queueProcessor->run($tasksProcessor, 'tasks:queue');

exit($exitCode);

