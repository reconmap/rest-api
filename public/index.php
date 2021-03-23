<?php declare(strict_types=1);

$applicationDir = realpath('../');

require $applicationDir . '/vendor/autoload.php';

use Fig\Http\Message\StatusCodeInterface;
use GuzzleHttp\Psr7\Response;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use League\Route\Http\Exception\NotFoundException;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Reconmap\ApiRouter;
use Reconmap\Services\ApplicationConfig;
use Reconmap\Services\ApplicationContainer;

$logger = new Logger('http');
$logsDirectory = $applicationDir . '/logs';
if (is_writable($logsDirectory)) {
    $applicationLogPath = $applicationDir . '/logs/application.log';
    $logger->pushHandler(new StreamHandler($applicationLogPath, Logger::DEBUG));
}

$configFilePath = $applicationDir . '/config.json';
if (!file_exists($configFilePath) || !is_readable($configFilePath)) {
    $errorMessage = 'Missing or unreadable API configuration file (config.json)';
    $logger->error($errorMessage);
    header($errorMessage, true, StatusCodeInterface::STATUS_SERVICE_UNAVAILABLE);
    echo $errorMessage, PHP_EOL;
    exit;
}

$config = ApplicationConfig::load($configFilePath);
$config->setAppDir($applicationDir);

$container = new ApplicationContainer($config, $logger);

$router = new ApiRouter();
$router->mapRoutes($container, $config);

try {
    $request = GuzzleHttp\Psr7\ServerRequest::fromGlobals();
    $response = $router->dispatch($request);
} catch (NotFoundException $e) {
    $logger->error($e->getMessage());
    $response = (new Response)->withStatus(StatusCodeInterface::STATUS_NOT_FOUND);
} catch (Exception $e) {
    $logger->error($e->getMessage());
    $response = (new Response)->withStatus(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
}

(new SapiEmitter)->emit($response);
