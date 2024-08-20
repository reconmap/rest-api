<?php declare(strict_types=1);

namespace Reconmap;

use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Reconmap\Services\QueueProcessor;
use Reconmap\Services\RedisServer;
use Reconmap\Tasks\ItemProcessor;

class QueueProcessorTest extends TestCase
{
    public function testSuccess()
    {
        $mockRedisServer = $this->createMock(RedisServer::class);
        $mockRedisServer->expects($this->exactly(2))
            ->method('brPop')
            ->with('some:queue', 1)
            ->willReturnOnConsecutiveCalls([1 => '{}'], null);

        $mockLogger = $this->createMock(Logger::class);

        $mockItemProcessor = $this->createMock(ItemProcessor::class);
        $mockItemProcessor->expects($this->once())
            ->method('process')
            ->with((object)[]);

        $processor = new QueueProcessor($mockRedisServer, $mockLogger);
        $processor->run($mockItemProcessor, 'some:queue');
    }
}
