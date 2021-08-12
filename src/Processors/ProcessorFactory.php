<?php declare(strict_types=1);

namespace Reconmap\Processors;

use Monolog\Logger;

class ProcessorFactory
{
    public function __construct(private Logger $logger)
    {
    }

    public function createByCommandShortName(string $shortName): ?VulnerabilityProcessor
    {
        $className = 'Reconmap\\Processors\\' . ucfirst($shortName) . 'OutputProcessor';

        if (class_exists($className)) {
            return new $className($this->logger);
        }

        return null;
    }
}
