<?php declare(strict_types=1);

namespace Reconmap\CommandOutputParsers;

use Reconmap\CommandOutputParsers\Models\Asset;
use Reconmap\CommandOutputParsers\Models\AssetKind;
use Reconmap\CommandOutputParsers\Models\ProcessorResult;
use Reconmap\CommandOutputParsers\Models\Vulnerability;

class MetasploitOutputProcessor extends AbstractOutputProcessor
{

    public function process(string $path): ProcessorResult
    {
        $result = new ProcessorResult();

        $xml = simplexml_load_file($path);
	if(!$xml) {
		return $result;
	}

        foreach ($xml->hosts->host as $rawHost) {
            $asset = new Asset(kind: AssetKind::Hostname, value: (string)$rawHost->name);

            foreach ($rawHost->vulns->vuln as $rawVulnerability) {
                $vulnerability = new Vulnerability();
                $vulnerability->summary = (string)$rawVulnerability->name;
                $vulnerability->risk = 'medium';
                // Dynamic props
                $vulnerability->asset = $asset;

                if ((string)$rawVulnerability->info !== 'NULL') {
                    $vulnerability->description = (string)$rawVulnerability->info;
                }

                $result->addVulnerability($vulnerability);
            }
        }

        return $result;
    }
}
