<?php declare(strict_types=1);

namespace Reconmap\Processors;

use Reconmap\Models\Vulnerability;

class ZapResultsProcessor extends AbstractCommandProcessor
{

    public function parseVulnerabilities(string $path): array
    {
        $vulnerabilities = [];

        $xml = simplexml_load_file($path);
        foreach ($xml->site->alerts->alertitem as $alertItem) {
            $vulnerability = new Vulnerability;
            $vulnerability->summary = (string)$alertItem->alert;
            $vulnerability->description = (string)$alertItem->desc;
            $vulnerability->solution = (string)$alertItem->solution;
            list($risk,) = sscanf((string)$alertItem->risk, '%s (%s)');
            if (!is_null($risk)) {
                $vulnerability->risk = strtolower($risk);
            }
            $vulnerabilities[] = $vulnerability;
        }

        return $vulnerabilities;
    }
}
