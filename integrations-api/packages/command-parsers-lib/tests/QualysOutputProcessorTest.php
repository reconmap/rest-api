<?php declare(strict_types=1);

namespace Reconmap\CommandOutputParsers;

class QualysOutputProcessorTest extends ParserTestCase
{
    public function testParsingVulnerabilities()
    {
        $processor = new QualysOutputProcessor();
        $processorResults = $processor->process($this->getResourceFilePath('qualys.xml'));
        $vulnerabilities = $processorResults->getVulnerabilities();
        $this->assertCount(91, $vulnerabilities);

        $vulnerability = $vulnerabilities[1];
        $this->assertEquals('A port scanner was used to draw a map of all the RPC services that are accessible.<P>', $vulnerability->summary);
        $this->assertEquals('111', $vulnerability->asset->getValue());
    }
}
