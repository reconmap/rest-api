<?php declare(strict_types=1);

namespace Reconmap\CommandOutputParsers;

class AcunetixOutputProcessorTest extends ParserTestCase
{
    public function testParsingVulnerabilities()
    {
        $processor = new AcunetixOutputProcessor();
        $processorResults = $processor->process($this->getResourceFilePath('acunetix-http.xml'));
        $vulnerabilities = $processorResults->getVulnerabilities();
        $this->assertCount(19, $vulnerabilities);

        $vulnerability = $vulnerabilities[0];
        $this->assertEquals('HTML form without CSRF protection', $vulnerability->summary);
    }
}
