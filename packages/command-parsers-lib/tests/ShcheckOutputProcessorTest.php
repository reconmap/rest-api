<?php declare(strict_types=1);

namespace Reconmap\CommandOutputParsers;

class ShcheckOutputProcessorTest extends ParserTestCase
{
    public function testParsingVulnerabilities()
    {
        $processor = new ShcheckOutputProcessor();
        $processorResults = $processor->process($this->getResourceFilePath('shcheck.json'));
        $vulnerabilities = $processorResults->getVulnerabilities();
        $this->assertCount(8, $vulnerabilities);

        $vulnerability = $vulnerabilities[4];
        $this->assertEquals('Missing security header: Permissions-Policy', $vulnerability->summary);
        $this->assertEquals(['headers'], $vulnerability->tags);
    }
}
