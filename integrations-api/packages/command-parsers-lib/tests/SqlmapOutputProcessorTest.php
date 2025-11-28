<?php declare(strict_types=1);

namespace Reconmap\CommandOutputParsers;

class SqlmapOutputProcessorTest extends ParserTestCase
{

    public function testParseVulnerabilities()
    {
        $processor = new SqlmapOutputProcessor();
        $result = $processor->process($this->getResourceFilePath('sqlmap-log-example.txt'));
        $vulnerabilities = $result->getVulnerabilities();

        $this->assertCount(1, $vulnerabilities);
        $this->assertEquals("SQL can be injected using parameter 'username (POST)'", $vulnerabilities[0]->description);
    }
}
