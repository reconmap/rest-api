<?php declare(strict_types=1);

namespace Reconmap\Http;

use GuzzleHttp\Psr7\Response;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Reconmap\Services\ApplicationConfig;

class CorsMiddlewareTest extends TestCase
{
    public function testAllowOneOrigin()
    {
        $mockLogger = $this->createMock(Logger::class);
        $mockApplicationConfig = $this->createMock(ApplicationConfig::class);
        $mockApplicationConfig->expects($this->once())
            ->method('getSettings')
            ->with('cors')
            ->willReturn(['allowedOrigins' => ['http://some.where']]);

        $mockResponse = new Response();

        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->once())
            ->method('hasHeader')
            ->with('Origin')
            ->willReturn(true);
        $mockRequest->expects($this->once())
            ->method('getHeaderLine')
            ->with('Origin')
            ->willReturn('http://some.where');

        $mockHandler = $this->createMock(RequestHandlerInterface::class);
        $mockHandler->expects($this->once())
            ->method('handle')
            ->with($mockRequest)
            ->willReturn($mockResponse);

        $middleware = new CorsMiddleware($mockLogger, $mockApplicationConfig);
        $response = $middleware->process($mockRequest, $mockHandler);

        $this->assertTrue($response->hasHeader('Access-Control-Allow-Origin'));
        $this->assertEquals('http://some.where', $response->getHeaderLine('Access-Control-Allow-Origin'));
    }

    public function testAllowAnyOrigin()
    {
        $mockLogger = $this->createMock(Logger::class);
        $mockApplicationConfig = $this->createMock(ApplicationConfig::class);
        $mockApplicationConfig->expects($this->once())
            ->method('getSettings')
            ->with('cors')
            ->willReturn(['allowedOrigins' => ['*']]);

        $mockResponse = new Response();

        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->never())
            ->method('hasHeader');
        $mockRequest->expects($this->never())
            ->method('getHeaderLine');

        $mockHandler = $this->createMock(RequestHandlerInterface::class);
        $mockHandler->expects($this->once())
            ->method('handle')
            ->with($mockRequest)
            ->willReturn($mockResponse);

        $middleware = new CorsMiddleware($mockLogger, $mockApplicationConfig);
        $response = $middleware->process($mockRequest, $mockHandler);

        $this->assertTrue($response->hasHeader('Access-Control-Allow-Origin'));
        $this->assertEquals('*', $response->getHeaderLine('Access-Control-Allow-Origin'));
    }
}
