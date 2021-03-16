<?php declare(strict_types=1);

namespace Reconmap\Processors;

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

            $vulnerability = (object)[
                'severity' => (string)$rawVulnerability['severity'],
                'summary' => (string)$rawVulnerability->name,
                'description' => preg_replace('/^ +/', '', (string)$rawVulnerability->issueDetail),
                'risk' => $risk,
                'solution' => $solution,
                'host' => (string)$rawVulnerability->host
            ];
            $vulnerabilities[] = $vulnerability;
        }

        return $vulnerabilities;
    }
}
