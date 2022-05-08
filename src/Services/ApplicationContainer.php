<?php declare(strict_types=1);

namespace Reconmap\Services;

use League\Container\Container;
use League\Container\ContainerAwareInterface;
use League\Container\ReflectionContainer;
use Monolog\Logger;
use Reconmap\Controllers\Controller;
use Reconmap\Database\ConnectionFactory;
use Reconmap\Repositories\MysqlRepository;

class ApplicationContainer extends Container
{
    public function __construct(ApplicationConfig $config, Logger $logger)
    {
        parent::__construct();

        $this->initialise($config, $logger);
    }

    public function initialise(ApplicationConfig $config, Logger $logger)
    {
        $this->delegate(new ReflectionContainer);

        $this->add(Logger::class, $logger);

        $this->add(\mysqli::class, ConnectionFactory::createConnection($config));

        $this->inflector(Controller::class)
            ->invokeMethod('setLogger', [$logger]);

        $this->inflector(MysqlRepository::class)
            ->invokeMethod('setLogger', [$logger]);

        $this->inflector(ConfigConsumer::class)
            ->invokeMethod('setConfig', [ApplicationConfig::class]);
        $this->add(ApplicationConfig::class, $config);

        $this->inflector(ContainerAwareInterface::class)
            ->invokeMethod('setContainer', [Container::class]);

        $this->add(Container::class, $this);
    }
}

