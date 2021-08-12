<?php declare(strict_types=1);

namespace Reconmap\Processors;

use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class BurpProProcessorTest extends TestCase
{

    public function testParseVulnerabilities()
    {
        $mockLogger = $this->createMock(Logger::class);

        $processor = new BurpproOutputProcessor($mockLogger);
        $vulnerabilities = $processor->parseVulnerabilities(__DIR__ . '/burp-2.1.02.xml');

        $this->assertCount(17, $vulnerabilities);
        $this->assertEquals('Strict Transport Security Misconfiguration', $vulnerabilities[5]->summary);
    }
}
