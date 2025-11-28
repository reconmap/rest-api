<?php declare(strict_types=1);

namespace Reconmap\Services\Logging;

use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Reconmap\Services\ApplicationConfig;

class LoggingConfiguratorTest extends TestCase
{
    public function testDefaultHandlersDisabled(): void
    {
        $fakeLoggingConfig = [
            'file' => [
                'enabled' => false,
                'path' => 'logs/app.log'
            ],
            'gelf' => [
                'enabled' => false,
                'serverName' => 'foo',
                'serverPort' => 12202
            ]
        ];

        $mockLogger = $this->createMock(Logger::class);
        $mockLogger->expects($this->never())
            ->method('pushHandler');

        $mockAppConfig = $this->createMock(ApplicationConfig::class);
        $mockAppConfig->expects($this->once())
            ->method('getSettings')
            ->willReturn($fakeLoggingConfig);

        $subject = new LoggingConfigurator($mockLogger, $mockAppConfig);
        $subject->configure();
    }

    public function testDefaultHandlers(): void
    {
        $fakeLoggingConfig = [
            'file' => [
                'enabled' => true,
                'path' => 'logs/app.log'
            ],
            'gelf' => [
                'enabled' => true,
                'serverName' => 'foo',
                'serverPort' => 12202
            ]
        ];

        $mockLogger = $this->createMock(Logger::class);
        $mockLogger->expects($this->exactly(2))
            ->method('pushHandler');

        $mockAppConfig = $this->createMock(ApplicationConfig::class);
        $mockAppConfig->expects($this->once())
            ->method('getSettings')
            ->willReturn($fakeLoggingConfig);

        $subject = new LoggingConfigurator($mockLogger, $mockAppConfig);
        $subject->configure();
    }

    public function testErrorHandler(): void
    {
        $mockLogger = $this->createMock(Logger::class);
        $mockLogger->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Something went wrong'));

        $mockAppConfig = $this->createMock(ApplicationConfig::class);
        $mockAppConfig->expects($this->once())
            ->method('getSettings')
            ->willReturn([]);

        $subject = new LoggingConfigurator($mockLogger, $mockAppConfig);
        $subject->configure();

        trigger_error('Something went wrong', E_USER_ERROR);
    }

    protected function tearDown(): void
    {
        restore_error_handler();
    }
}
