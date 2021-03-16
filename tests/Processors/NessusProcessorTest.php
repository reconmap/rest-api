<?php declare(strict_types=1);

namespace Reconmap\Processors;

use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class NessusProcessorTest extends TestCase
{

    public function testParseVulnerabilities()
    {
        $mockLogger = $this->createMock(Logger::class);

        $processor = new NessusProcessor($mockLogger);
        $vulnerabilities = $processor->parseVulnerabilities(__DIR__ . '/nessus-2.xml');
        $this->assertCount(5, $vulnerabilities);
        $this->assertEquals('Protect your target with an IP filter.', $vulnerabilities[4]->solution);
    }

    public function testParseVulnerabilitiesIncludingCvssData()
    {
        $mockLogger = $this->createMock(Logger::class);

        $processor = new NessusProcessor($mockLogger);
        $vulnerabilities = $processor->parseVulnerabilities(__DIR__ . '/nessus-1.xml');

        $this->assertCount(288, $vulnerabilities);
        $this->assertEquals(5.1, $vulnerabilities[8]->cvss_score);
        $this->assertEquals('CVSS2#AV:N/AC:H/Au:N/C:P/I:P/A:P', $vulnerabilities[8]->cvss_vector);
    }
}
