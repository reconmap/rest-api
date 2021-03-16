<?php declare(strict_types=1);

namespace Reconmap\Processors;

use Monolog\Logger;

abstract class AbstractCommandProcessor implements VulnerabilityProcessor
{
    public function __construct(protected Logger $logger)
    {
    }
}
