<?php declare(strict_types=1);

namespace Reconmap\CommandOutputParsers;

use Reconmap\CommandOutputParsers\Models\ProcessorResult;
use Reconmap\CommandOutputParsers\Models\Vulnerability;

class AcunetixOutputProcessor extends AbstractOutputProcessor
{

    public function process(string $path): ProcessorResult
    {
        $result = new ProcessorResult();

        $xml = simplexml_load_file($path);
	if(!$xml) {
		return $result;
	}

        foreach ($xml->Scan->ReportItems->ReportItem as $reportItem) {
            $vulnerability = new Vulnerability();
            $vulnerability->summary = (string)$reportItem->Name;
            $vulnerability->description = (string)$reportItem->Details;
            $vulnerability->remediation = (string)$reportItem->Recommendation;
            $result->addVulnerability($vulnerability);
        }

        return $result;
    }
}
