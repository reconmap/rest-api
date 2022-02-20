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
use Reconmap\Services\Filesystem\ApplicationLogFilePath;

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
$applicationLogFilePath = new ApplicationLogFilePath($config);
$logsDirectory = $applicationLogFilePath->getDirectory();
if (is_writable($logsDirectory)) {
    $applicationLogPath = $applicationDir . '/logs/application.log';
    $logger->pushHandler(new StreamHandler($applicationLogPath, Logger::DEBUG));
}

set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline) use ($logger) {
    if (E_USER_ERROR === $errno) {
        $logger->error("$errstr ($errno) on $errfile:$errline");
    } else {
        $logger->warning("$errstr ($errno) on $errfile:$errline");
    }
});

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
