<?php declare(strict_types=1);

namespace Reconmap\CommandOutputParsers;

use Reconmap\CommandOutputParsers\Models\Asset;
use Reconmap\CommandOutputParsers\Models\AssetKind;
use Reconmap\CommandOutputParsers\Models\ProcessorResult;
use Reconmap\CommandOutputParsers\Models\Vulnerability;

class QualysOutputProcessor extends AbstractOutputProcessor
{
    public function process(string $path): ProcessorResult
    {
        $hosts = [];

        $result = new ProcessorResult();

        $xml = simplexml_load_file($path);
	if(!$xml) {
		return $result;
	}
        foreach ($xml->HOST_LIST->HOST as $rawHost) {
            $host = new Asset(AssetKind::Hostname, (string)$rawHost->DNS);
            $result->addAsset($host);

            foreach ($rawHost->VULN_INFO_LIST->VULN_INFO as $vulnInfo) {
                $type = (string)$vulnInfo->TYPE;
                $qid = (string)$vulnInfo->QID;
                if ($type === 'Ig' && isset($vulnInfo->PORT)) { // Information gathering
                    $portAsset = new Asset(AssetKind::Port, (string)$vulnInfo->PORT);
                    $host->addChild($portAsset);
                    $hosts[(string)$vulnInfo->QID] = $portAsset;
                } elseif ($type === 'Vuln') {
                    $hosts[$qid] = $host;
                }
            }
        }

        foreach ($xml->GLOSSARY->VULN_DETAILS_LIST->VULN_DETAILS as $rawVuln) {
            $vulnerability = new Vulnerability();
            $vulnerability->risk = match ((string)$rawVuln->severity) {
                '1' => 'low',
                '3' => 'high',
                default => 'medium',
            };
            $vulnerability->summary = trim((string)$rawVuln->THREAT);
            $vulnerability->impact = (string)$rawVuln->IMPACT;
            $vulnerability->remediation = (string)$rawVuln->SOLUTION;

            $qid = (string)$rawVuln->QID;
            if (isset($hosts[$qid])) {
                $vulnerability->asset = $hosts[$qid];
            }

            $result->addVulnerability($vulnerability);
        }

        return $result;
    }
}
