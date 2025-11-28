<?php declare(strict_types=1);

namespace Reconmap\CommandOutputParsers;

use PHPUnit\Framework\TestCase;

class ProcessorFactoryTest extends TestCase
{
    private ProcessorFactory $processorFactory;

    protected function setUp(): void
    {
        $this->processorFactory = new ProcessorFactory();
    }

    public function testInvalidCommand()
    {
        $this->assertNull($this->processorFactory->createFromOutputParserName('foobar'));
    }

    public static function commandDataProvider(): array
    {
        return [
            ['burppro', BurpproOutputProcessor::class],
            ['metasploit', MetasploitOutputProcessor::class],
            ['nessus', NessusOutputProcessor::class],
            ['nmap', NmapOutputProcessor::class],
            ['nuclei', NucleiOutputProcessor::class],
            ['sqlmap', SqlmapOutputProcessor::class],
            ['zap', ZapOutputProcessor::class],
        ];
    }

    /**
     * @dataProvider commandDataProvider
     */
    public function testNessusCommand(string $commandShortName, string $classFqn)
    {
        $this->assertInstanceOf($classFqn, $this->processorFactory->createFromOutputParserName($commandShortName));
    }

    public function testGettingAll()
    {
        $this->assertTrue(in_array('nessus', array_column($this->processorFactory->getAll(), 'code')));
    }
}
