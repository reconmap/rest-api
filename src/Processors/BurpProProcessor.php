<?php declare(strict_types=1);

namespace Reconmap\Processors;

use Reconmap\Models\Vulnerability;

class BurpProProcessor extends AbstractCommandProcessor
{
    public function parseVulnerabilities(string $path): array
    {
        $vulnerabilities = [];

        $xml = simplexml_load_file($path);
        $this->logger->debug('Burp version: ' . (string)$xml['burpVersion']);

        foreach ($xml->issue as $rawVulnerability) {
            $pluginName = (string)$rawVulnerability->plugin_name;
            if ('Nessus Scan Information' === $pluginName) continue;

            $solution = (string)$rawVulnerability->remediationDetail;
            if (empty($solution)) $solution = null;

            $risk = strtolower((string)$rawVulnerability->risk_factor);

            $vulnerability = new Vulnerability();
            $vulnerability->summary = (string)$rawVulnerability->name;
            $vulnerability->description = preg_replace('/^ +/', '', (string)$rawVulnerability->issueDetail);
            $vulnerability->risk = $risk;
            $vulnerability->solution = $solution;

            // Dynamic props
            $vulnerability->host = (object)['name' => (string)$rawVulnerability->host];
            $vulnerability->severity = (string)$rawVulnerability['severity'];

            $vulnerabilities[] = $vulnerability;
        }

        return $vulnerabilities;
    }
}
