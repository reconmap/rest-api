<?php declare(strict_types=1);

namespace Reconmap\Processors;

use Monolog\Logger;

abstract class AbstractCommandParser
{
    public function __construct(protected Logger $logger)
    {
    }
}
