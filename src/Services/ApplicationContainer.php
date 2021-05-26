<?php declare(strict_types=1);

namespace Reconmap\Services;

use League\Container\Container;
use League\Container\ReflectionContainer;
use Monolog\Logger;
use Reconmap\Controllers\Controller;
use Reconmap\DatabaseFactory;

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

        $this->add(\mysqli::class, DatabaseFactory::createConnection($config));

        $this->inflector(Controller::class)
            ->invokeMethod('setLogger', [$logger]);

        $this->inflector(ConfigConsumer::class)
            ->invokeMethod('setConfig', [ApplicationConfig::class]);
        $this->add(ApplicationConfig::class, $config);

        $this->inflector(ContainerConsumer::class)
            ->invokeMethod('setContainer', [Container::class]);
        $this->add(Container::class, $this);
    }
}

