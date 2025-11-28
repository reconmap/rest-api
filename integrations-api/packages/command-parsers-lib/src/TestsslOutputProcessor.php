<?php declare(strict_types=1);

namespace Reconmap\CommandOutputParsers;

use Reconmap\CommandOutputParsers\Models\Asset;
use Reconmap\CommandOutputParsers\Models\AssetKind;
use Reconmap\CommandOutputParsers\Models\ProcessorResult;
use Reconmap\Models\Vulnerability;

/**
 * @see https://github.com/testssl/testssl.sh
 */
class TestsslOutputProcessor extends AbstractOutputProcessor
{
    public function getCommandUsageExample(): string
    {
        return 'docker run --rm -ti -v $PWD:/data --workdir /data drwetter/testssl.sh --jsonfile testssl-output.json https://localhost';
    }

    public function process(string $path): ProcessorResult
    {
        $result = new ProcessorResult();

        $fileContent = file_get_contents($path);
        if (!json_validate($fileContent)) {
            throw new \Exception('Invalid JSON: ' . $path);
        }
        $json = json_decode($fileContent);
        unset($fileContent);

        foreach ($json as $rawVulnerability) {
            $severity = strtolower($rawVulnerability->severity);
            if (in_array($severity, ['ok', 'info'])) continue;

            $vulnerability = new Vulnerability();
            $vulnerability->summary = $rawVulnerability->id . ': ' . $rawVulnerability->finding;
            $vulnerability->severity = $severity;
            $vulnerability->tags = [];
            if (isset($rawVulnerability->cve)) {
                $vulnerability->tags[] = $rawVulnerability->cve;
            }
            if (isset($rawVulnerability->cwe)) {
                $vulnerability->tags[] = $rawVulnerability->cwe;
            }

            $vulnerability->asset = new Asset(kind: AssetKind::Hostname, value: explode('/', $rawVulnerability->ip)[0]);;

            $result->addVulnerability($vulnerability);
        }

        return $result;
    }
}
