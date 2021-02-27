<?php

declare(strict_types=1);

namespace Reconmap\Services;

use League\Container\Inflector\InflectorInterface;
use League\Container\ReflectionContainer;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Reconmap\DatabaseTestCase;

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

        /** @var ApplicationContainer $container */
        $container = $this->getMockBuilder(ApplicationContainer::class)
            ->onlyMethods(['delegate', 'add', 'inflector'])
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects($this->once())
            ->method('delegate')
            ->with($this->isInstanceOf(ReflectionContainer::class));
        $container->expects($this->atLeastOnce())
            ->method('add');
        $container->expects($this->atLeastOnce())
            ->method('inflector')
            ->willReturn($this->getMockForAbstractClass(InflectorInterface::class));

        $container->initialise($config, $logger);
    }
}
