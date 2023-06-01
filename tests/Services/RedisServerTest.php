<?php declare(strict_types=1);

namespace Reconmap\Services;

use PHPUnit\Framework\TestCase;
use Reconmap\ConsecutiveParamsTrait;

class RedisServerTest extends TestCase
{
    use ConsecutiveParamsTrait;

    public function testAuth()
    {
        $mockEnvironment = $this->createMock(Environment::class);
        $mockEnvironment->expects($this->exactly(4))
            ->method('getValue')
            ->with(...$this->consecutiveParams(['REDIS_HOST'], ['REDIS_PORT'], ['REDIS_USER'], ['REDIS_PASSWORD']))
            ->willReturnOnConsecutiveCalls('localhost', '1111', 'root', 'roto');

        $redisServer = $this->getMockBuilder(RedisServer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['connect', 'auth'])
            ->getMock();
        $redisServer->expects($this->once())
            ->method('connect')
            ->with('localhost', 1111)
            ->willReturn(true);
        $redisServer->expects($this->once())
            ->method('auth')
            ->with(['root', 'roto'])
            ->willReturn(true);

        $redisServer->__construct($mockEnvironment);
    }
}
