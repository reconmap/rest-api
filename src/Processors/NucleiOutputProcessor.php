<?php declare(strict_types=1);

namespace Reconmap\Processors;

use Reconmap\Models\Vulnerability;

class NucleiOutputProcessor extends AbstractCommandParser implements VulnerabilityParser
{

    public function parseVulnerabilities(string $path): array
    {
        $vulnerabilities = [];

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
            $host = [
                'name' => $json->host
            ];

            $vulnerability = new Vulnerability();
            $vulnerability->summary = $json->info->name;
            $vulnerability->description = $line;
            $vulnerability->tags = explode(',', $json->info->tags);
            $vulnerability->severity = $json->info->severity;

            // Dynamic props
            $vulnerability->host = (object)$host;
            $vulnerability->severity = (string)$json->info->severity;

            $vulnerabilities[] = $vulnerability;
        }

        return $vulnerabilities;
    }
}
