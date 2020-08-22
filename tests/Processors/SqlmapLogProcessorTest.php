<?php

declare(strict_types=1);

namespace Reconmap\Processors;

use PHPUnit\Framework\TestCase;

class SqlmapLogProcessorTest extends TestCase
{

    public function testGetOpenPorts()
    {
        $processor = new SqlmapLogProcessor(__DIR__ . '/sqlmap-log-example.txt');
        $openPorts = $processor->parseOpenPorts();
        $this->assertContains(3306, $openPorts);
        $this->assertContains(8080, $openPorts);
    }
}
