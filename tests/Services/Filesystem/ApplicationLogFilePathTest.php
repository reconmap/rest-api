<?php declare(strict_types=1);

namespace Reconmap\Services\Filesystem;

use PHPUnit\Framework\TestCase;
use Reconmap\Services\ApplicationConfig;

class ApplicationLogFilePathTest extends TestCase
{
    public function testPathGeneration()
    {
        $mockConfig = $this->createMock(ApplicationConfig::class);
        $mockConfig->expects($this->once())
            ->method('getAppDir')
            ->willReturn('/this/path');

        $appFilePath = new ApplicationLogFilePath($mockConfig);
        $this->assertEquals('/this/path/logs', $appFilePath->getDirectory());
    }
}
