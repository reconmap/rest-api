<?php declare(strict_types=1);

namespace Reconmap\Processors;

use Reconmap\Models\Vulnerability;

class NessusOutputProcessor extends AbstractCommandParser implements VulnerabilityParser
{
    /**
     * @param string $path
     * @return array<Vulnerability>
     */
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

                $remediation = (string)$rawVulnerability->solution;
                if ('n/a' === $remediation) $remediation = null;

                $risk = strtolower((string)$rawVulnerability->risk_factor);

                $vulnerability = new Vulnerability();
                $vulnerability->summary = (string)$rawVulnerability->synopsis;
                $vulnerability->description = preg_replace('/^ +/', '', (string)$rawVulnerability->description);
                $vulnerability->risk = $risk;
                $vulnerability->remediation = $remediation;
                // Dynamic props
                $vulnerability->host = (object)$host;
                $vulnerability->severity = (string)$rawVulnerability['severity'];

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
