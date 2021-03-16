<?php declare(strict_types=1);

namespace Reconmap\Processors;

class NessusProcessor extends AbstractCommandProcessor
{

    public function parseVulnerabilities(string $path): array
    {
        $vulnerabilities = [];

        $xml = simplexml_load_file($path);

        foreach ($xml->Report->ReportHost as $rawHost) {
            $host = [
                'name' => (string)$rawHost['name']
            ];

            foreach ($rawHost->ReportItem as $rawVulnerability) {
                $pluginName = (string)$rawVulnerability->plugin_name;
                if ('Nessus Scan Information' === $pluginName) continue;

                $solution = (string)$rawVulnerability->solution;
                if ('n/a' === $solution) $solution = null;

                $risk = strtolower((string)$rawVulnerability->risk_factor);

                $vulnerability = (object)[
                    'severity' => (string)$rawVulnerability['severity'],
                    'summary' => (string)$rawVulnerability->synopsis,
                    'description' => preg_replace('/^ +/', '', (string)$rawVulnerability->description),
                    'risk' => $risk,
                    'solution' => $solution,
                    'host' => (object)$host
                ];

                if (isset($rawVulnerability->cvss_base_score)) {
                    $vulnerability->cvss_score = (float)$rawVulnerability->cvss_base_score;
                }
                if (isset($rawVulnerability->cvss_vector)) {
                    $vulnerability->cvss_vector = (string)$rawVulnerability->cvss_vector;
                }

                $vulnerabilities[] = $vulnerability;
            }
        }

        return $vulnerabilities;
    }
}
