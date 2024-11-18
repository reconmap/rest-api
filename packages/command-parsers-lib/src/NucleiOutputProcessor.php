<?php declare(strict_types=1);

namespace Reconmap\CommandOutputParsers;

use Reconmap\CommandOutputParsers\Models\Asset;
use Reconmap\CommandOutputParsers\Models\AssetKind;
use Reconmap\CommandOutputParsers\Models\ProcessorResult;
use Reconmap\CommandOutputParsers\Models\Vulnerability;

class NucleiOutputProcessor extends AbstractOutputProcessor
{

    public function process(string $path): ProcessorResult
    {
        $result = new ProcessorResult();

        $lines = file($path);
        foreach ($lines as $line) {
            /*
             {
            "templateID":"robots-txt",
            "info":{
                "name":"robots.txt file",
                "author":"CasperGN",
                "severity":"info",
                "tags":"misc,generic"
            },
            "type":"http",
            "host":"https://reconmap.com",
            "matched":"https://reconmap.com/robots.txt",
            "ip":"188.166.137.55",
            "timestamp":"2021-08-19T21:16:47.969895638Z"
            }
             */
            $json = json_decode($line);

            $hostAsset = new Asset(kind: AssetKind::Hostname, value: $json->host);

            $vulnerability = new Vulnerability();
            $vulnerability->summary = $json->info->name;
            $vulnerability->description = $line;
            $vulnerability->tags = explode(',', $json->info->tags);
            $vulnerability->severity = $json->info->severity;
            $vulnerability->severity = (string)$json->info->severity;

            $vulnerability->asset = $hostAsset;

            $result->addVulnerability($vulnerability);
        }

        return $result;
    }
}
