<?php declare(strict_types=1);

$applicationDir = realpath('../');

require $applicationDir . '/vendor/autoload.php';

use Fig\Http\Message\StatusCodeInterface;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Monolog\Logger;
use Reconmap\ApiRouter;
use Reconmap\Services\ApplicationConfig;
use Reconmap\Services\ApplicationContainer;
use Reconmap\Services\Logging\LoggingConfigurator;

$configFilePath = $applicationDir . '/config.json';
if (!file_exists($configFilePath) || !is_readable($configFilePath)) {
    $errorMessage = 'Missing or unreadable API configuration file (config.json)';
    header($errorMessage, true, StatusCodeInterface::STATUS_SERVICE_UNAVAILABLE);
    echo $errorMessage, PHP_EOL;
    exit;
}

$config = ApplicationConfig::load($configFilePath);
$config->setAppDir($applicationDir);

$logger = new Logger('http');
$loggingConfigurator = new LoggingConfigurator($logger, $config);
$loggingConfigurator->configure();

$container = new ApplicationContainer($config, $logger);

$router = new ApiRouter();
$router->mapRoutes($container, $config);

$request = GuzzleHttp\Psr7\ServerRequest::fromGlobals();

$response = $router->dispatch($request);

(new SapiEmitter)->emit($response);

