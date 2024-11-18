<?php declare(strict_types=1);

namespace Reconmap\CommandOutputParsers;

use Reconmap\CommandOutputParsers\Models\Asset;
use Reconmap\CommandOutputParsers\Models\AssetKind;
use Reconmap\CommandOutputParsers\Models\ProcessorResult;

class SubfinderOutputProcessor extends AbstractOutputProcessor
{

    public function process(string $path): ProcessorResult
    {
        $result = new ProcessorResult();

        $lines = file($path);
        foreach ($lines as $line) {
            /*
             {
{"host":"mail.rmap.org","source":"crtsh"}
{"host":"www.rmap.org","source":"crtsh"}

            }
             */
            $json = json_decode($line);

            $hostAsset = new Asset(kind: AssetKind::Hostname, value: $json->host);

            $result->addAsset($hostAsset);
        }

        return $result;
    }
}
