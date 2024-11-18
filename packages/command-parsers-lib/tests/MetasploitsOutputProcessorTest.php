<?php declare(strict_types=1);

namespace Reconmap\CommandOutputParsers;

class MetasploitsOutputProcessorTest extends ParserTestCase
{

    public function testParseVulnerabilities()
    {
        $processor = new MetasploitOutputProcessor();
        $result = $processor->process($this->getResourceFilePath('metasploit.xml'));
        $vulnerabilities = $result->getVulnerabilities();

        $this->assertCount(2, $vulnerabilities);
        $this->assertEquals('exploit/windows/smb/ms08_067_netapi', $vulnerabilities[1]->summary);
    }
}
