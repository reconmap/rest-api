<?php declare(strict_types=1);

namespace Reconmap\CommandOutputParsers;

use League\HTMLToMarkdown\HtmlConverter;
use Reconmap\CommandOutputParsers\Models\Asset;
use Reconmap\CommandOutputParsers\Models\AssetKind;
use Reconmap\CommandOutputParsers\Models\ProcessorResult;
use Reconmap\CommandOutputParsers\Models\Vulnerability;

class BurpproOutputProcessor extends AbstractOutputProcessor
{
    public function process(string $path): ProcessorResult
    {
        $result = new ProcessorResult();

        $xml = simplexml_load_file($path);
	if(!$xml) {
		return $result;
	}
        $markdown = new HtmlConverter();

        foreach ($xml->issue as $rawVulnerability) {
            $pluginName = (string)$rawVulnerability->plugin_name;
            if ('Nessus Scan Information' === $pluginName) continue;

            $remediation = (string)$rawVulnerability->remediationDetail;
            if (empty($remediation)) $remediation = null;

            $vulnerability = new Vulnerability();

            $risk = strtolower((string)$rawVulnerability->risk_factor);
            $vulnerability->risk = $risk;

            $vulnerability->severity = (string)$rawVulnerability['severity'];
            $vulnerability->summary = (string)$rawVulnerability->name;
            $htmlDescription = (string)$rawVulnerability->issueDetail;
            $description = $markdown->convert($htmlDescription);
            $vulnerability->description = preg_replace('/^ +/', '', $description);
            $vulnerability->remediation = $remediation;

            $vulnerability->asset = new Asset(kind: AssetKind::Hostname, value: (string)$rawVulnerability->host);

            $result->addVulnerability($vulnerability);
        }

        return $result;
    }
}
