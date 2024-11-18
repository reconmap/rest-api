<?php declare(strict_types=1);

namespace Reconmap\CommandOutputParsers;

class TestsslOutputProcessorTest extends ParserTestCase
{
    public function testParsingVulnerabilities()
    {
        $processor = new TestsslOutputProcessor;
        $processorResults = $processor->process($this->getResourceFilePath('testssl.json'));
        $vulnerabilities = $processorResults->getVulnerabilities();
        $this->assertCount(5, $vulnerabilities);

        $vulnerability = $vulnerabilities[4];
        $this->assertEquals('potentially vulnerable, uses TLS CBC ciphers', $vulnerability->summary);
        $this->assertEquals('low', $vulnerability->severity);
        $this->assertEquals('example.org', $vulnerability->asset->getValue());
    }
}
