<?php declare(strict_types=1);

$applicationDir = dirname(__DIR__, 2);

require $applicationDir . '/vendor/autoload.php';

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Reconmap\QueueProcessor;
use Reconmap\Services\ApplicationConfig;
use Reconmap\Services\ApplicationContainer;
use Reconmap\Tasks\EmailTaskProcessor;

$logger = new Logger('cron');
$logger->pushHandler(new StreamHandler($applicationDir . '/logs/application.log', Logger::DEBUG));

$configFilePath = $applicationDir . '/config.json';
$config = ApplicationConfig::load($configFilePath);
$config->setAppDir($applicationDir);

$container = new ApplicationContainer($config, $logger);

$emailTaskProcessor = $container->get(EmailTaskProcessor::class);

/** @var QueueProcessor $queueProcessor */
$queueProcessor = $container->get(QueueProcessor::class);
$exitCode = $queueProcessor->run($emailTaskProcessor, 'email:queue');

exit($exitCode);

