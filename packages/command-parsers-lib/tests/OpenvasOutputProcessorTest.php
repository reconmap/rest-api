<?php declare(strict_types=1);

namespace Reconmap\CommandOutputParsers;

use Reconmap\CommandOutputParsers\Models\Asset;
use Reconmap\CommandOutputParsers\Models\AssetKind;

class OpenvasOutputProcessorTest extends ParserTestCase
{
    private OpenvasOutputProcessor $processor;

    public function setUp(): void
    {
        $this->processor = new OpenvasOutputProcessor();
    }

    public static function dataProviderTestFiles(): array
    {
        return [
            ['openvas-test0.xml', 6],
            ['openvas-test1.xml', 20],
            ['openvas-test2.xml', 26],
        ];
    }

    /**
     * @dataProvider dataProviderTestFiles
     */
    public function testVulnerabilitiesParsing(string $fileName, int $expectedVulnerabilitiesCount)
    {
        $result = $this->processor->process($this->getResourceFilePath($fileName));
        $vulnerabilities = $result->getVulnerabilities();

        $this->assertCount($expectedVulnerabilitiesCount, $vulnerabilities);
    }

    public function testParseHostsAndPorts()
    {
        $result = $this->processor->process($this->getResourceFilePath('openvas-test1.xml'));
        $vulnerabilities = $result->getVulnerabilities();

        $host = new Asset(AssetKind::Hostname, '192.168.122.230');
        $host->addChild(new Asset(AssetKind::Port, '135/tcp'));

        $this->assertEquals($host, $vulnerabilities[0]->asset);

    }

    public function testParseVulnerabilities()
    {
        $result = $this->processor->process($this->getResourceFilePath('openvas-test0.xml'));
        $vulnerabilities = $result->getVulnerabilities();

        $this->assertCount(6, $vulnerabilities);
        $this->assertEquals('The configuration of this services should be changed sothat it does not support the listed weak ciphers anymore.', $vulnerabilities[4]->remediation);
        $this->assertEquals('This routine search for weak SSL ciphers offered by a service.', $vulnerabilities[4]->summary);
    }

    public function testParseVulnerabilitiesIncludingCvssData()
    {
        $result = $this->processor->process($this->getResourceFilePath('openvas-test0.xml'));
        $vulnerabilities = $result->getVulnerabilities();

        $this->assertCount(6, $vulnerabilities);
        $this->assertEquals('AV:N/AC:M/Au:N/C:N/I:P/A:N', $vulnerabilities[5]->cvss_vector);
    }
}
