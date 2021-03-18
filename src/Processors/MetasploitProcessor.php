<?php declare(strict_types=1);

namespace Reconmap\Processors;

class MetasploitProcessor extends AbstractCommandProcessor
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
                $vulnerability = (object)[
                    'summary' => (string)$rawVulnerability->name,
                    'risk' => 'medium',
                    'host' => (object)$host
                ];

                if ((string)$rawVulnerability->info !== 'NULL') {
                    $vulnerability['description'] = (string)$rawVulnerability->info;
                }

                $vulnerabilities[] = $vulnerability;
            }
        }

        return $vulnerabilities;
    }
}
