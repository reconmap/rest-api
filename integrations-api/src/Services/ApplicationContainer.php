<?php declare(strict_types=1);

namespace Reconmap\Services;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Reconmap\CommandOutputParsers\ProcessorFactory;
use Reconmap\Database\ConnectionFactory;
use Reconmap\Database\MysqlServer;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Filesystem\Filesystem;

class ApplicationContainer extends ContainerBuilder
{
    public function __construct(?ParameterBagInterface $parameterBag = null)
    {
        parent::__construct($parameterBag);

        $loader = new PhpFileLoader($this, new FileLocator(__DIR__));

        $instanceof = [];

        $configurator = new ContainerConfigurator($this, $loader, $instanceof, dirname(__FILE__, 2), '');
        $this->configure($configurator);

        $this->register(Filesystem::class);
    }

    public static function initialise(ContainerInterface $container, ApplicationConfig $config, LoggerInterface $logger): void
    {
        $container->set(ApplicationConfig::class, $config);
        $container->set(MysqlServer::class, ConnectionFactory::createConnection($config));
        $container->set(LoggerInterface::class, $logger);
        $container->set(ProcessorFactory::class, new ProcessorFactory());
        $container->set(ContainerInterface::class, $container);
        $container->set(EventDispatcher::class, new EventDispatcher());
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
            ->load('Reconmap\\Repositories\\', $prefix . 'Repositories/*')
            ->load('Reconmap\\Database\\', $prefix . 'Database/*')
            ->load('Reconmap\\Tasks\\', $prefix . 'Tasks/*')
            ->load('Reconmap\\Http\\', $prefix . 'Http/*')
            ->load('Reconmap\\Controllers\\', $prefix . 'Controllers/*')
            ->set(ServerRequestInterface::class)->synthetic()
            ->set(LoggerInterface::class)->synthetic()
            ->set(ApplicationConfig::class)->synthetic()
            ->set(ProcessorFactory::class)->synthetic()
            ->set(ContainerInterface::class)->synthetic()
            ->set(EventDispatcher::class)->synthetic()
            ->set(MysqlServer::class)->synthetic();
    }
}

