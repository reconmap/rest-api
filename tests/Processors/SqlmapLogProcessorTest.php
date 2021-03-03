<?php declare(strict_types=1);

namespace Reconmap\Processors;

use PHPUnit\Framework\TestCase;

class SqlmapLogProcessorTest extends TestCase
{

    public function testParseVulnerabilities()
    {
        $processor = new SqlmapProcessor();
        $vulnerabilities = $processor->parseVulnerabilities(__DIR__ . '/sqlmap-log-example.txt');
        $this->assertCount(1, $vulnerabilities);
        $this->assertEquals("SQL can be injected using parameter 'username (POST)'", $vulnerabilities[0]->description);
    }
}
