<?php declare(strict_types=1);

$applicationDir = realpath('../');

require $applicationDir . '/vendor/autoload.php';

use Fig\Http\Message\StatusCodeInterface;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Monolog\Logger;
use Reconmap\ApiRouter;
use Reconmap\Controllers\System\GetOpenApiYamlController;
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

$routes = new \Symfony\Component\Routing\RouteCollection();
$routes->add('GetOpenApiYaml', new \Symfony\Component\Routing\Route('/openapi.json', [
        '_controller' => $container->get(GetOpenApiYamlController::class)
    ]
));

$router = new ApiRouter();
$router->mapRoutes($container, $config);

$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
$matcher = new \Symfony\Component\Routing\Matcher\UrlMatcher($routes, new \Symfony\Component\Routing\RequestContext());

$dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
$dispatcher->addSubscriber(new \Symfony\Component\HttpKernel\EventListener\RouterListener($matcher, new \Symfony\Component\HttpFoundation\RequestStack()));
$controllerResolver = new \Symfony\Component\HttpKernel\Controller\ControllerResolver();
$argumentResolver = new \Symfony\Component\HttpKernel\Controller\ArgumentResolver();

$kernel = new \Symfony\Component\HttpKernel\HttpKernel($dispatcher, $controllerResolver, new \Symfony\Component\HttpFoundation\RequestStack(), $argumentResolver);

try {
    $response = $kernel->handle($request);
    $response->send();
} catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $nfe) {
    $request = GuzzleHttp\Psr7\ServerRequest::fromGlobals();

    $response = $router->dispatch($request);

    (new SapiEmitter)->emit($response);
} catch (Exception $e) {
    print_r($e);
    die($e->getMessage());
}

$kernel->terminate($request, $response);
