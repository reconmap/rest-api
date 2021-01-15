<?php

declare(strict_types=1);

namespace Reconmap\Processors;

class ProcessorFactory
{

    public function createByTaskType(string $type): ?VulnerabilityProcessor
    {
        switch ($type) {
            case 'nmap':
                return new NmapResultsProcessor();
            case 'sqlmap':
                return new SqlmapProcessor();
        }

        return null;
    }
}
