<?php

declare(strict_types=1);

$applicationDir = dirname(__DIR__, 2);
require $applicationDir . '/vendor/autoload.php';

use Monolog\Logger;
use Reconmap\Cli\Commands\EmailProcessorCommand;
use Reconmap\Cli\Commands\TaskProcessorCommand;
use Reconmap\Cli\Commands\TestDataGeneratorCommand;
use Reconmap\Cli\Commands\WeeklyEmailReportSenderCommand;
use Reconmap\Services\ApplicationConfig;
use Reconmap\Services\ApplicationContainer;
use Reconmap\Services\Logging\LoggingConfigurator;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;

$configFilePath = $applicationDir . '/config.json';

$config = ApplicationConfig::load($configFilePath);
$config->setAppDir($applicationDir);

$logger = new Logger('cron');
$loggingConfigurator = new LoggingConfigurator($logger, $config);
$loggingConfigurator->configure();

if (in_array('--use-test-database', $argv)) {

    $config['database'] = [
        'host' => 'rmap-mysql',
        'username' => 'reconmapper',
        'password' => 'reconmapped',
        'name' => 'reconmap_test'
    ];
    unset($argv[array_search('--use-test-database', $argv)]);
}

$container = new ApplicationContainer();
$container->compile();
ApplicationContainer::initialise($container, $config, $logger);

$app = new Application('Reconmap internal CLI');
$app->addCommand($container->get(EmailProcessorCommand::class));
$app->addCommand($container->get(TaskProcessorCommand::class));
$app->addCommand($container->get(TestDataGeneratorCommand::class));
$app->addCommand($container->get(WeeklyEmailReportSenderCommand::class));
$app->run(new ArgvInput($argv));
