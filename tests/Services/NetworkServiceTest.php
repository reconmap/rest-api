<?php declare(strict_types=1);

namespace Reconmap\Services;

use PHPUnit\Framework\TestCase;

class NetworkServiceTest extends TestCase
{
    public function testHttpClientIp()
    {
        $networkService = new NetworkService([
            'HTTP_CLIENT_IP' => '127.0.0.1',
            'HTTP_X_FORWARDED_FOR' => '192.168.0.1',
            'REMOTE_ADDR' => '8.8.8.8',
        ]);
        $this->assertEquals('127.0.0.1', $networkService->getClientIp());
    }

    public function testHttpForwardedFor()
    {
        $networkService = new NetworkService([
            'HTTP_X_FORWARDED_FOR' => '192.168.0.1',
            'REMOTE_ADDR' => '8.8.8.8',
        ]);
        $this->assertEquals('192.168.0.1', $networkService->getClientIp());
    }

    public function testHttpForwardedForWithProxies()
    {
        $networkService = new NetworkService([
            'HTTP_X_FORWARDED_FOR' => '172.0.0.1, 192.168.0.1, 192.0.0.1',
            'REMOTE_ADDR' => '8.8.8.8',
        ]);
        $this->assertEquals('172.0.0.1', $networkService->getClientIp());
    }

    public function testRemoteAddress()
    {
        $networkService = new NetworkService([
            'REMOTE_ADDR' => '8.8.8.8',
        ]);
        $this->assertEquals('8.8.8.8', $networkService->getClientIp());
    }
}
