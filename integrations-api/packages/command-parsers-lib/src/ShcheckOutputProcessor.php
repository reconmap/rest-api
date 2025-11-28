<?php declare(strict_types=1);

namespace Reconmap\CommandOutputParsers;

use Reconmap\CommandOutputParsers\Models\ProcessorResult;
use Reconmap\Models\Vulnerability;

class ShcheckOutputProcessor extends AbstractOutputProcessor
{
    public function getCommandUsageExample(): string
    {
        return 'shcheck.py --json-output http://localhost';
    }

    public function process(string $path): ProcessorResult
    {
        $result = new ProcessorResult();

        /**
         * {
         * "https://www.foobar.com": {
         * "present": {
         * "Strict-Transport-Security": "max-age=63072000"
         * },
         * "missing": [
         * "X-Frame-Options",
         * "X-Content-Type-Options",
         * "Content-Security-Policy",
         * "Referrer-Policy",
         * "Permissions-Policy",
         * "Cross-Origin-Embedder-Policy",
         * "Cross-Origin-Resource-Policy",
         * "Cross-Origin-Opener-Policy"
         * ]
         * }
         * }
         */

        $output = json_decode(file_get_contents($path), associative: true);
        foreach ($output as $value) {
            if (isset($value['missing']) && is_array($value['missing'])) {
                foreach ($value['missing'] as $missingHeader) {
                    $vulnerability = new Vulnerability();
                    $vulnerability->summary = "Missing security header: $missingHeader";
                    $vulnerability->description = "Missing security header: $missingHeader";
                    $vulnerability->tags = ['headers'];

                    $result->addVulnerability($vulnerability);
                }
            }
        }

        return $result;
    }
}
