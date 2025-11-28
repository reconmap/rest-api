<?php

declare(strict_types=1);

namespace Reconmap\Services;

use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Reconmap\DatabaseTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ApplicationContainerTest extends TestCase
{

    public function testDelegationIsSetup()
    {
        $config = $this->createMock(ApplicationConfig::class);
        $config->expects($this->atLeastOnce())
            ->method('getSettings')
            ->with('database')
            ->willReturn(DatabaseTestCase::DATABASE_SETTINGS);
        $logger = $this->createMock(Logger::class);

        /** @var ContainerBuilder $container */
        $mockContainer = $this->createMock(ContainerBuilder::class);
        $mockContainer->expects($this->atLeastOnce())
            ->method('set');

        $container = new ApplicationContainer();
        $container->initialise($mockContainer, $config, $logger);
    }
}
