<?php declare(strict_types=1);

namespace Reconmap\CommandOutputParsers;

use Reconmap\CommandOutputParsers\Models\ProcessorResult;
use Reconmap\CommandOutputParsers\Models\Vulnerability;

class SqlmapOutputProcessor extends AbstractOutputProcessor
{

    public function process(string $path): ProcessorResult
    {
        $result = new ProcessorResult();

        $logContent = file_get_contents($path);

        if (stripos($logContent, 'sqlmap identified the following injection point(s)') !== false) {
            preg_match('/Parameter: (.+)/', $logContent, $matches);
            $parameter = $matches[1];
            $vulnerability = new Vulnerability;
            $vulnerability->summary = "SQL injection";
            $vulnerability->description = "SQL can be injected using parameter '$parameter'";

            $result->addVulnerability($vulnerability);
        }

        return $result;
    }
}
