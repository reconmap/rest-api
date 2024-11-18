<?php declare(strict_types=1);

namespace Reconmap\CommandOutputParsers;

use Reconmap\CommandOutputParsers\Models\Asset;
use Reconmap\CommandOutputParsers\Models\AssetKind;
use Reconmap\CommandOutputParsers\Models\ProcessorResult;

class NmapOutputProcessor extends AbstractOutputProcessor
{

    public function process(string $path): ProcessorResult
    {
        $result = new ProcessorResult();

        $xml = simplexml_load_file($path);
	if(!$xml) {
		return $result;
	}
        foreach ($xml->host as $host) {
            $hostAddress = (string)$host->address['addr'];
            $hostAsset = new Asset(kind: AssetKind::Hostname, value: $hostAddress);
            $hostAsset->addTag((string)$host->address['addrtype']);

            foreach ($host->ports->port as $port) {
                if ((string)$port->state['state'] == 'open') {
                    $portNumber = (int)$port['portid'];

                    $portAsset = new Asset(kind: AssetKind::Port, value: strval($portNumber));
                    $hostAsset->addChild($portAsset);
                }
            }

            $result->addAsset($hostAsset);
        }

        return $result;
    }
}
