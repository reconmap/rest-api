<?php declare(strict_types=1);

namespace Reconmap\CommandOutputParsers;

class NucleiOutputProcessorTest extends ParserTestCase
{
    public function testParsingVulnerabilities()
    {
        $processor = new NucleiOutputProcessor();
        $processorResults = $processor->process($this->getResourceFilePath('nuclei.jsonl'));
        $vulnerabilities = $processorResults->getVulnerabilities();
        $this->assertCount(1, $vulnerabilities);

        $vulnerability = $vulnerabilities[0];
        $this->assertEquals('robots.txt file', $vulnerability->summary);
    }
}
