<?php declare(strict_types=1);

namespace Reconmap\CommandOutputParsers;

use Reconmap\CommandOutputParsers\Models\Asset;
use Reconmap\CommandOutputParsers\Models\AssetKind;
use Reconmap\CommandOutputParsers\Models\ProcessorResult;
use Reconmap\CommandOutputParsers\Models\Vulnerability;

class OpenvasOutputProcessor extends AbstractOutputProcessor
{
    public function process(string $path): ProcessorResult
    {
        $result = new ProcessorResult();

        $xml = simplexml_load_file($path);
	if(!$xml) {
		return $result;
	}
        foreach ($xml->report->results->result as $rawHost) {
            $hostAsset = new Asset(kind: AssetKind::Hostname, value: (string)$rawHost->host);

            if (!empty($rawHost->port)) {
                $portAsset = new Asset(kind: AssetKind::Port, value: (string)$rawHost->port);
                $hostAsset->addChild($portAsset);
            }

            foreach ($rawHost->nvt as $rawVulnerability) {
                $vulnerability = new Vulnerability();
                $vulnerability->summary = (string)$rawVulnerability->name . ' - ' . (string)$rawVulnerability->cve;

                $tags = explode('|', (string)$rawVulnerability->tags);
                foreach ($tags as $tag) {
                    list($key, $value) = explode('=', $tag);
                    switch ($key) {
                        case 'cvss_base_vector':
                            $vulnerability->cvss_vector = $value;
                            break;
                        case 'summary':
                            $vulnerability->summary = str_replace("\n", "", $value);
                            break;
                        case 'solution':
                            $vulnerability->remediation = str_replace("\n", "", $value);
                            break;
                    }
                }


                $vulnerability->external_refs = (string)$rawVulnerability->xref;

                // @todo process refs/ref type[url] id
                $vulnerability->description = preg_replace('/^ +/', '', (string)$rawVulnerability->description);
                $vulnerability->severity = (string)$rawVulnerability->severity;

                $risk = strtolower((string)$rawVulnerability->threat);
                $vulnerability->risk = $risk;

                $vulnerability->asset = $hostAsset;

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
