<?php declare(strict_types=1);

namespace Reconmap\Tasks;

use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Reconmap\Services\ApplicationConfig;

class EmailTaskProcessorTest extends TestCase
{
    public function testSuccess()
    {
        $mockSettings = ['host' => '', 'port' => '', 'username' => '', 'password' => ''];

        $mockAppConfig = $this->createMock(ApplicationConfig::class);
        $mockAppConfig->expects($this->once())
            ->method('getSettings')
            ->willReturn($mockSettings);

        $mockLogger = $this->createMock(Logger::class);

        $mockItem = new \stdClass();

        $controller = new EmailTaskProcessor($mockAppConfig, $mockLogger);
        $controller->process($mockItem);
    }
}
