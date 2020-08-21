<?php

declare(strict_types=1);

namespace Reconmap\Processors;

class NmapResultsProcessor
{

    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function parseOpenPorts(): array
    {
        $xml = simplexml_load_file($this->path);
        $openPorts = [];
        foreach($xml->host->ports->port as $port) {
            if((string)$port->state['state'] == 'open') {
                $openPorts[] = (int)$port['portid'];
            }
        }
        return $openPorts;
    }
}
