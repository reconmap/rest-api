<?php declare(strict_types=1);

namespace Reconmap\Services;

use Monolog\Logger;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\CommandOutputParsers\ProcessorFactory;
use Reconmap\Database\ConnectionFactory;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\Filesystem\Filesystem;

class ApplicationContainer extends ContainerBuilder
{
    public function __construct(ApplicationConfig $config, Logger $logger)
    {
        parent::__construct();

        $this->initialise($config, $logger);
    }

    public function initialise(ApplicationConfig $config, Logger $logger)
    {
        $loader = new PhpFileLoader($this, new FileLocator(__DIR__));

        $instanceof = [];

        $configurator = new ContainerConfigurator($this, $loader, $instanceof, dirname(__FILE__, 2), '');
        $this->configure($configurator);

        $this->register(Filesystem::class);
        $this->compile();
        $this->set(ApplicationConfig::class, $config);
        $this->set(\mysqli::class, ConnectionFactory::createConnection($config));
        $this->set(Logger::class, $logger);
        $this->set(ProcessorFactory::class, new ProcessorFactory());
    }

    private function configure(ContainerConfigurator $containerConfigurator): void
    {
        $services = $containerConfigurator->services();

        $prefix = '../';
        $services->defaults()
            ->autowire()
            ->autoconfigure()
            ->public()
            ->load('Reconmap\\Cli\\Commands\\', $prefix . 'Cli/Commands/*')
            ->load('Reconmap\\Services\\', $prefix . 'Services/*')
            ->exclude([$prefix . 'Services/QueryParams/OrderByRequestHandler.php'])
            ->load('Reconmap\\Repositories\\', $prefix . 'Repositories/*')
            ->load('Reconmap\\Http\\', $prefix . 'Http/*')
            ->exclude($prefix . 'Http/ApplicationRequest.php')
            ->load('Reconmap\\Controllers\\', $prefix . 'Controllers/*')
            ->set(ServerRequestInterface::class)
            ->synthetic()
            ->set(Logger::class)
            ->synthetic()
            ->set(ApplicationConfig::class)
            ->synthetic()
            ->set(ProcessorFactory::class)
            ->synthetic()
            ->set(\mysqli::class)
            ->synthetic();
    }
}

