<?php declare(strict_types=1);

namespace Reconmap\CommandOutputParsers;

use Reconmap\CommandOutputParsers\Models\Asset;
use Reconmap\CommandOutputParsers\Models\AssetKind;
use Reconmap\CommandOutputParsers\Models\ProcessorResult;
use Reconmap\CommandOutputParsers\Models\Vulnerability;

class TestsslOutputProcessor extends AbstractOutputProcessor
{
    public function process(string $path): ProcessorResult
    {
        $result = new ProcessorResult();

        $json = json_decode(file_get_contents($path));
        foreach ($json->scanResult as $scanResult) {
            $hostAsset = new Asset(kind: AssetKind::Hostname, value: $scanResult->targetHost);

            foreach ($scanResult->vulnerabilities as $rawVulnerability) {
                $severity = strtolower($rawVulnerability->severity);
                if (in_array($severity, ['ok', 'info'])) continue;

                $vulnerability = new Vulnerability();
                $vulnerability->summary = $rawVulnerability->finding;
                $vulnerability->severity = $severity;
                $vulnerability->tags = [];
                if (isset($rawVulnerability->cve)) {
                    $vulnerability->tags[] = $rawVulnerability->cve;
                }
                if (isset($rawVulnerability->cwe)) {
                    $vulnerability->tags[] = $rawVulnerability->cwe;
                }

                $vulnerability->asset = $hostAsset;

                $result->addVulnerability($vulnerability);
            }
        }

        return $result;
    }
}
