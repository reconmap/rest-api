<?php
declare(strict_types=1);

namespace Reconmap\Services;

use League\Container\Container;
use League\Container\ReflectionContainer;
use Monolog\Logger;
use Reconmap\DatabaseFactory;

class ApplicationContainer extends Container
{
    public function __construct(Config $config, Logger $logger)
    {
        parent::__construct();
        $this->initialise($config, $logger);
    }

    public function initialise(Config $config, Logger $logger)
    {
        $this->delegate(new ReflectionContainer);

        $this->add(Logger::class, $logger);

        $this->inflector(ConfigConsumer::class)
            ->invokeMethod('setConfig', [Config::class]);
        $this->add(Config::class, $config);

        $this->inflector(ContainerConsumer::class)
            ->invokeMethod('setContainer', [Container::class]);
        $this->add(Container::class, $this);

        $this->add(\mysqli::class, DatabaseFactory::createConnection($config));
    }
}

