<?php declare(strict_types=1);

namespace Reconmap\CommandOutputParsers;

use Reconmap\CommandOutputParsers\Models\AssetKind;

class NmapOutputProcessorTest extends ParserTestCase
{

    public function testParseVulnerabilities()
    {
        $processor = new NmapOutputProcessor();
        $result = $processor->process($this->getResourceFilePath('nmap-output-example.xml'));
        $assets = $result->getAssets();

        $this->assertCount(1, $assets);
        $this->assertEquals(['ipv4'], $assets[0]->getTags());
        $this->assertCount(4, $assets[0]->getChildren());
        $this->assertEquals(AssetKind::Port, $assets[0]->getChildren()[0]->getKind());
        $this->assertEquals(631, $assets[0]->getChildren()[0]->getValue());
        $this->assertEquals(22, $assets[0]->getChildren()[1]->getValue());
        $this->assertEquals(3306, $assets[0]->getChildren()[2]->getValue());
    }
}
