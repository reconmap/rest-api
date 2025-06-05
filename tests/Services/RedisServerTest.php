<?php declare(strict_types=1);

namespace Reconmap\Services;

use PHPUnit\Framework\TestCase;
use Reconmap\ConsecutiveParamsTrait;

class RedisServerTest extends TestCase
{
    use ConsecutiveParamsTrait;

    public function testAuth()
    {
        $applicationConfigMock = $this->createMock(ApplicationConfig::class);
        $applicationConfigMock->expects($this->once())
            ->method('getSettings')
            ->with('redis')
            ->willReturn(['host' => '127.0.0.1', 'port' => 6379, 'username' => 'user', 'password' => 'pass']);

        $redisServer = $this->getMockBuilder(RedisServer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['connect', 'auth'])
            ->getMock();
        $redisServer->expects($this->once())
            ->method('connect')
            ->with('127.0.0.1', 6379)
            ->willReturn(true);
        $redisServer->expects($this->once())
            ->method('auth')
            ->with(['user', 'pass'])
            ->willReturn(true);

        $redisServer->__construct($applicationConfigMock);
    }
}
