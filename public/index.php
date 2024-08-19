<?php declare(strict_types=1);

$applicationDir = realpath('../');

require $applicationDir . '/vendor/autoload.php';

use Monolog\Logger;
use Reconmap\ApiRouter;
use Reconmap\Services\ApplicationConfig;
use Reconmap\Services\ApplicationContainer;
use Reconmap\Services\Logging\LoggingConfigurator;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpFoundation\Response;

$configFilePath = $applicationDir . '/config.json';
if (!file_exists($configFilePath) || !is_readable($configFilePath)) {
    $errorMessage = 'Missing or unreadable API configuration file (config.json)';
    header($errorMessage, true, Response::HTTP_SERVICE_UNAVAILABLE);
    echo $errorMessage, PHP_EOL;
    exit;
}

$applicationConfig = ApplicationConfig::load($configFilePath);
$applicationConfig->setAppDir($applicationDir);

$logger = new Logger('http');
$loggingConfigurator = new LoggingConfigurator($logger, $applicationConfig);
$loggingConfigurator->configure();

$container = new ApplicationContainer($applicationConfig, $logger);

$request = GuzzleHttp\Psr7\ServerRequest::fromGlobals();
$container->set(Psr\Http\Message\ServerRequestInterface::class, $request);

$router = new ApiRouter();
$router->mapRoutes($container, $applicationConfig);

$response = $router->dispatch($request);

$httpFoundationFactory = new HttpFoundationFactory();
$symfonyResponse = $httpFoundationFactory->createResponse($response);

$symfonyResponse->send();

