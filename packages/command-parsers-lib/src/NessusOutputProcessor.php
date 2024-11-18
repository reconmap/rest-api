<?php declare(strict_types=1);

namespace Reconmap\CommandOutputParsers;

use Reconmap\CommandOutputParsers\Models\Asset;
use Reconmap\CommandOutputParsers\Models\AssetKind;
use Reconmap\CommandOutputParsers\Models\ProcessorResult;
use Reconmap\CommandOutputParsers\Models\Vulnerability;

class NessusOutputProcessor extends AbstractOutputProcessor
{
    public function process(string $path): ProcessorResult
    {
        $result = new ProcessorResult();

        $xml = simplexml_load_file($path);
	if(!$xml) {
		return $result;
	}
        foreach ($xml->Report->ReportHost as $rawHost) {
            $asset = new Asset(kind: AssetKind::Hostname, value: (string)$rawHost['name']);

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
                $vulnerability->severity = (string)$rawVulnerability['severity'];

                $vulnerability->asset = $asset;

                if (isset($rawVulnerability->cvss_base_score)) {
                    $vulnerability->cvss_score = (float)$rawVulnerability->cvss_base_score;
                }
                if (isset($rawVulnerability->cvss_vector)) {
                    $vulnerability->cvss_vector = (string)$rawVulnerability->cvss_vector;
                }

                $result->addVulnerability($vulnerability);
            }
        }

        return $result;
    }
}
