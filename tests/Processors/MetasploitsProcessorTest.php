<?php declare(strict_types=1);

namespace Reconmap\Processors;

use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class MetasploitsProcessorTest extends TestCase
{

    public function testParseVulnerabilities()
    {
        $mockLogger = $this->createMock(Logger::class);

        $processor = new MetasploitOutputProcessor($mockLogger);
        $vulnerabilities = $processor->parseVulnerabilities(__DIR__ . '/metasploit.xml');

        $this->assertCount(2, $vulnerabilities);
        $this->assertEquals('exploit/windows/smb/ms08_067_netapi', $vulnerabilities[1]->summary);
    }
}
