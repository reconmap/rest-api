<?php declare(strict_types=1);

$applicationDir = realpath('../');

require $applicationDir . '/vendor/autoload.php';

use Monolog\Logger;
use Reconmap\ApiRouter;
use Reconmap\Services\ApplicationConfig;
use Reconmap\Services\ApplicationContainer;
use Reconmap\Services\Logging\LoggingConfigurator;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\HttpFoundation\Response;

$configFilePath = $applicationDir . '/config.json';
if (!file_exists($configFilePath) || !is_readable($configFilePath)) {
    $errorMessage = 'Missing or unreadable API configuration file (config.json)';
    header($errorMessage, true, Response::HTTP_SERVICE_UNAVAILABLE);
    echo $errorMessage, PHP_EOL;
    exit;
}

$config = ApplicationConfig::load($configFilePath);
$config->setAppDir($applicationDir);

$logger = new Logger('http');
$loggingConfigurator = new LoggingConfigurator($logger, $config);
$loggingConfigurator->configure();

$file = $config->getAppDir() . '/data/attachments/container.php';
if (file_exists($file)) {
    require $file;
    $container = new CachedApplicationContainer();
} else {
    $container = new ApplicationContainer();
    $container->compile();

    $dumper = new PhpDumper($container);
    file_put_contents($file, $dumper->dump(['class' => 'CachedApplicationContainer']));
}
ApplicationContainer::initialise($container, $config, $logger);

$request = GuzzleHttp\Psr7\ServerRequest::fromGlobals();
$container->set(Psr\Http\Message\ServerRequestInterface::class, $request);

$router = new ApiRouter();
$router->mapRoutes($container, $config);

$response = $router->dispatch($request);

$httpFoundationFactory = new HttpFoundationFactory();
$symfonyResponse = $httpFoundationFactory->createResponse($response);

$symfonyResponse->send();

