<?php declare(strict_types=1);

namespace Reconmap\CommandOutputParsers;

use Reconmap\CommandOutputParsers\Models\AssetKind;

class SubfinderOutputProcessorTest extends ParserTestCase
{
    public function testParsingVulnerabilities()
    {
        $processor = new SubfinderOutputProcessor();
        $processorResults = $processor->process($this->getResourceFilePath('subfinder.jsonl'));
        $assets = $processorResults->getAssets();
        $this->assertCount(2, $assets);

        $this->assertEquals('mail.rmap.org', $assets[0]->getValue());
        $this->assertEquals(AssetKind::Hostname, $assets[0]->getKind());
    }
}
