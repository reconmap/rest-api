<?php declare(strict_types=1);

namespace Reconmap\Processors;

use Reconmap\Models\Vulnerability;

class MetasploitOutputProcessor extends AbstractCommandProcessor
{

    public function parseVulnerabilities(string $path): array
    {
        $vulnerabilities = [];

        $xml = simplexml_load_file($path);

        foreach ($xml->hosts->host as $rawHost) {
            $host = [
                'name' => (string)$rawHost->name
            ];

            foreach ($rawHost->vulns->vuln as $rawVulnerability) {
                $vulnerability = new Vulnerability();
                $vulnerability->summary = (string)$rawVulnerability->name;
                $vulnerability->risk = 'medium';
                // Dynamic props
                $vulnerability->host = (object)$host;

                if ((string)$rawVulnerability->info !== 'NULL') {
                    $vulnerability->description = (string)$rawVulnerability->info;
                }

                $vulnerabilities[] = $vulnerability;
            }
        }

        return $vulnerabilities;
    }
}
