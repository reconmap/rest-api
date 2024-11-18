<?php declare(strict_types=1);

namespace Reconmap\CommandOutputParsers;

use Reconmap\CommandOutputParsers\Models\ProcessorResult;
use Reconmap\CommandOutputParsers\Models\Vulnerability;

class ZapOutputProcessor extends AbstractOutputProcessor
{

    public function process(string $path): ProcessorResult
    {
        $result = new ProcessorResult();

        $xml = simplexml_load_file($path);
	if(!$xml) {
		return $result;
	}
        foreach ($xml->site->alerts->alertitem as $alertItem) {
            $vulnerability = new Vulnerability;
            $vulnerability->summary = (string)$alertItem->alert;
            $vulnerability->description = (string)$alertItem->desc;
            $vulnerability->remediation = (string)$alertItem->solution;
            list($risk,) = sscanf((string)$alertItem->risk, '%s (%s)');
            if (!is_null($risk)) {
                $vulnerability->risk = strtolower($risk);
            }

            $result->addVulnerability($vulnerability);
        }

        return $result;
    }
}
