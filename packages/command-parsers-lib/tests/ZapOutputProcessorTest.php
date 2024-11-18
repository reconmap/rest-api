<?php declare(strict_types=1);

namespace Reconmap\CommandOutputParsers;

class ZapOutputProcessorTest extends ParserTestCase
{
    public function testParsingVulnerabilities()
    {
        $processor = new ZapOutputProcessor();
        $processorResults = $processor->process($this->getResourceFilePath('zap-report-merged.xml'));
        $vulnerabilities = $processorResults->getVulnerabilities();
        $this->assertCount(10, $vulnerabilities);

        $vulnerability = $vulnerabilities[1];
        $this->assertEquals('X-Content-Type-Options Header Missing', $vulnerability->summary);
    }
}
