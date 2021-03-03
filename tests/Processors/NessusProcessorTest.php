<?php declare(strict_types=1);

namespace Reconmap\Processors;

use PHPUnit\Framework\TestCase;

class NessusProcessorTest extends TestCase
{

    public function testParseVulnerabilities()
    {
        $processor = new NessusProcessor();
        $vulnerabilities = $processor->parseVulnerabilities(__DIR__ . '/nessus-2.xml');
        $this->assertCount(5, $vulnerabilities);
        $this->assertEquals("Protect your target with an IP filter.", $vulnerabilities[4]->solution);
    }
}
