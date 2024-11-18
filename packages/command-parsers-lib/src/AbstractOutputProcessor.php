<?php declare(strict_types=1);

namespace Reconmap\CommandOutputParsers;

use Reconmap\CommandOutputParsers\Models\ProcessorResult;

abstract class AbstractOutputProcessor
{
    abstract public function process(string $path): ProcessorResult;
}
