<?php declare(strict_types=1);

$applicationDir = realpath('../');

require $applicationDir . '/vendor/autoload.php';

use Monolog\Logger;
use Reconmap\ApiRouter;
use Reconmap\Events\SearchEvent;
use Reconmap\Services\ApplicationConfig;
use Reconmap\Services\ApplicationContainer;
use Reconmap\Services\Logging\LoggingConfigurator;
use Reconmap\Services\SearchListener;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Response;

// Load the configuration
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

$file = $config->getAppDir() . '/data/cache/container.php';
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

$guzzleRequest = GuzzleHttp\Psr7\ServerRequest::fromGlobals();
$container->set(Psr\Http\Message\ServerRequestInterface::class, $guzzleRequest);

$apiRouter = new ApiRouter();
$apiRouter->mapRoutes($container, $config);

/**
 * @var EventDispatcher $eventDispatcher
 */
$eventDispatcher = $container->get(EventDispatcher::class);
$eventDispatcher->addListener(SearchEvent::class, $container->get(SearchListener::class));

$apiResponse = $apiRouter->dispatch($guzzleRequest);

$httpFoundationFactory = new HttpFoundationFactory();
$response = $httpFoundationFactory->createResponse($apiResponse);

$response->send();
