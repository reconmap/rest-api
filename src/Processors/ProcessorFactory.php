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
        $processors = [
            'nmap' => NmapResultsProcessor::class,
            'sqlmap' => SqlmapProcessor::class,
            'nessus' => NessusProcessor::class,
            'burppro' => BurpProProcessor::class,
            'metasploit' => MetasploitProcessor::class,
        ];

        if (array_key_exists($shortName, $processors)) {
            return new $processors[$shortName]($this->logger);
        }

        return null;
    }
}
