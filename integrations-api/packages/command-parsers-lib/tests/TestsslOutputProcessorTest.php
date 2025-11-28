<?php declare(strict_types=1);

namespace Reconmap\CommandOutputParsers;

class TestsslOutputProcessorTest extends ParserTestCase
{
    public function testParsingVulnerabilities()
    {
        $processor = new TestsslOutputProcessor;
        $processorResults = $processor->process($this->getResourceFilePath('testssl.json'));
        $vulnerabilities = $processorResults->getVulnerabilities();
        $this->assertCount(4, $vulnerabilities);

        $vulnerability = $vulnerabilities[3];
        $this->assertEquals('BREACH: potentially VULNERABLE, gzip HTTP compression detected  - only supplied \'/\' tested', $vulnerability->summary);
        $this->assertEquals('medium', $vulnerability->severity);
        $this->assertEquals('localhost', $vulnerability->asset->getValue());
    }
}
