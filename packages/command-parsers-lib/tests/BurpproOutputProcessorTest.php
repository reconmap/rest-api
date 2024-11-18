<?php declare(strict_types=1);

namespace Reconmap\CommandOutputParsers;

class BurpproOutputProcessorTest extends ParserTestCase
{

    public function testParseVulnerabilities()
    {
        $processor = new BurpproOutputProcessor();
        $result = $processor->process($this->getResourceFilePath('burp-2.1.02.xml'));

        $vulnerabilities = $result->getVulnerabilities();
        $this->assertCount(17, $vulnerabilities);
        $this->assertEquals('Strict Transport Security Misconfiguration', $vulnerabilities[5]->summary);
    }
}
