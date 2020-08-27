<?php

declare(strict_types=1);

namespace Reconmap\Processors;

use PHPUnit\Framework\TestCase;

class NmapResultsProcessorTest extends TestCase
{

    public function testParseVulnerabilities()
    {
        $processor = new NmapResultsProcessor();
        $vulnerabilities = $processor->parseVulnerabilities(__DIR__ . '/nmap-output-example.xml');
        $this->assertCount(4, $vulnerabilities);
        $this->assertEquals("Port '3306' is open", $vulnerabilities[2]->description);
        $this->assertEquals("Port '8080' is open", $vulnerabilities[3]->description);
    }
}
