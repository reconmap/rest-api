<?php declare(strict_types=1);

namespace Reconmap\Processors;

use League\HTMLToMarkdown\HtmlConverter;
use Reconmap\Models\Vulnerability;

class BurpproOutputProcessor extends AbstractCommandParser implements VulnerabilityParser
{
    public function parseVulnerabilities(string $path): array
    {
        $vulnerabilities = [];

        $xml = simplexml_load_file($path);
        $this->logger->debug('Burp version: ' . (string)$xml['burpVersion']);

        $markdown = new HtmlConverter();

        foreach ($xml->issue as $rawVulnerability) {
            $pluginName = (string)$rawVulnerability->plugin_name;
            if ('Nessus Scan Information' === $pluginName) continue;

            $remediation = (string)$rawVulnerability->remediationDetail;
            if (empty($remediation)) $remediation = null;

            $risk = strtolower((string)$rawVulnerability->risk_factor);

            $vulnerability = new Vulnerability();
            $vulnerability->summary = (string)$rawVulnerability->name;
            $htmlDescription = (string)$rawVulnerability->issueDetail;
            $description = $markdown->convert($htmlDescription);
            $vulnerability->description = preg_replace('/^ +/', '', $description);
            $vulnerability->risk = $risk;
            $vulnerability->remediation = $remediation;

            // Dynamic props
            $vulnerability->host = (object)['name' => (string)$rawVulnerability->host];
            $vulnerability->severity = (string)$rawVulnerability['severity'];

            $vulnerabilities[] = $vulnerability;
        }

        return $vulnerabilities;
    }
}
