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
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Loader\AttributeClassLoader;
use Symfony\Component\Routing\Loader\AttributeDirectoryLoader;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Router;

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

$request = Request::createFromGlobals();
$requestContext = new RequestContext();
$requestContext->fromRequest($request);

$controllersDir = $applicationDir . '/src/Controllers';

$loader = new AttributeDirectoryLoader(
    new FileLocator([$controllersDir]),
    new class extends AttributeClassLoader {
        protected function configureRoute(Route $route, \ReflectionClass $class, \ReflectionMethod $method, object $annot): void
        {
            $route->setDefault('_controller', $class->name . '::' . $method->name);
        }
    }
);

$routes = $loader->load($controllersDir);
$router = new Router(
    $loader,
    $controllersDir,
    ['cache_dir' => $applicationDir . '/data/cache', 'debug' => false],
    $requestContext
);

try {
    // Try routing the request with Symfony's Router
    $parameters = $router->match($request->getPathInfo());
    $controller = $parameters['_controller'];
    unset($parameters['_controller'], $parameters['_route']);

    // Call the matched controller
    [$class, $method] = explode('::', $controller, 2);
    $ooo = $container->get($class);
    $response = call_user_func_array([$ooo, $method], $parameters);
} catch (ResourceNotFoundException $e) {
    // Fall back to the custom API router
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
}

$response->send();
