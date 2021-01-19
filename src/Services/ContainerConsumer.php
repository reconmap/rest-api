<?php declare(strict_types=1);

namespace Reconmap\Services;

use Psr\Container\ContainerInterface;

interface ContainerConsumer
{

    public function setContainer(ContainerInterface $container): void;
}
