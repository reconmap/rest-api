<?php declare(strict_types=1);

namespace Reconmap\Processors;

use Reconmap\Models\Vulnerability;

class NmapResultsProcessor implements VulnerabilityProcessor
{

    public function parseVulnerabilities(string $path): array
    {
        $vulnerabilities = [];

        $xml = simplexml_load_file($path);
        foreach ($xml->host->ports->port as $port) {
            if ((string)$port->state['state'] == 'open') {
                $portNumber = (int)$port['portid'];
                $vulnerability = new Vulnerability;
                $vulnerability->summary = "Open port";
                $vulnerability->description = "Port '$portNumber' is open";
                $vulnerabilities[] = $vulnerability;
            }
        }

        return $vulnerabilities;
    }
}
