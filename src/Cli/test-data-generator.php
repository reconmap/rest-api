<?php declare(strict_types=1);

$applicationDir = dirname(__DIR__, 2);

require $applicationDir . '/vendor/autoload.php';

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Reconmap\Database\TestDataGenerator;
use Reconmap\Services\ApplicationConfig;
use Reconmap\Services\ApplicationContainer;

$logger = new Logger('cron');
$logger->pushHandler(new StreamHandler($applicationDir . '/logs/application.log', Logger::DEBUG));

set_error_handler(function ($errno, $errstr, $errfile, $errline) use ($logger) {
    $logger->error("$errstr ($errno) on $errfile:$errline");
});

$configFilePath = $applicationDir . '/config.json';
$config = ApplicationConfig::load($configFilePath);
$config->setAppDir($applicationDir);

if (!in_array('--use-default-database', $argv)) {
    $config['database'] = [
        'host' => 'rmap-mysql',
        'username' => 'reconmapper',
        'password' => 'reconmapped',
        'name' => 'reconmap_test'
    ];
}

$container = new ApplicationContainer($config, $logger);

$generator = $container->get(TestDataGenerator::class);
$generator->generate();

