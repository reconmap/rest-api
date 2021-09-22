<?php declare(strict_types=1);

namespace Reconmap\Processors;

use Reconmap\Models\Vulnerability;

class NmapOutputProcessor extends AbstractCommandParser implements VulnerabilityParser
{

    public function parseVulnerabilities(string $path): array
    {
        $vulnerabilities = [];

        $xml = simplexml_load_file($path);
        foreach ($xml->host as $host) {
            $hostAddress = $host->address['addr'];
            foreach ($host->ports->port as $port) {
                if ((string)$port->state['state'] == 'open') {
                    $portNumber = (int)$port['portid'];
                    $vulnerability = new Vulnerability;
                    $vulnerability->summary = "Port $portNumber has been left open.";
                    $vulnerability->description = "The port $portNumber is open and could be used by an attacker to get into your system. Unless you need this port open consider shutting the service down or restricting access using a firewall.";

                    $vulnerability->host = (object)['name' => (string)$hostAddress];
                    $vulnerabilities[] = $vulnerability;
                }
            }
        }

        return $vulnerabilities;
    }
}
