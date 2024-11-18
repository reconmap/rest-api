<?php declare(strict_types=1);

namespace Reconmap\CommandOutputParsers;

class NessusOutputProcessorTest extends ParserTestCase
{

    public function testParseVulnerabilities()
    {
        $processor = new NessusOutputProcessor();
        $result = $processor->process($this->getResourceFilePath('nessus-2.xml'));
        $vulnerabilities = $result->getVulnerabilities();
        $this->assertCount(5, $vulnerabilities);
        $this->assertEquals('Protect your target with an IP filter.', $vulnerabilities[4]->remediation);
    }

    public function testParseVulnerabilitiesIncludingCvssData()
    {
        $processor = new NessusOutputProcessor();
        $result = $processor->process($this->getResourceFilePath('nessus-1.xml'));
        $vulnerabilities = $result->getVulnerabilities();

        $this->assertCount(288, $vulnerabilities);
        $this->assertEquals(5.1, $vulnerabilities[8]->cvss_score);
        $this->assertEquals('CVSS2#AV:N/AC:H/Au:N/C:P/I:P/A:P', $vulnerabilities[8]->cvss_vector);
    }
}
