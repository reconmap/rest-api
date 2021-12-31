<?php declare(strict_types=1);

namespace Reconmap\Http;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Reconmap\Services\ApplicationConfig;

class SecurityMiddlewareTest extends TestCase
{
    public function testSuccess()
    {
        $mockApplicationConfig = $this->createMock(ApplicationConfig::class);
        $mockApplicationConfig->expects($this->once())
            ->method('getSettings')
            ->with('cors')
            ->willReturn(['allowedOrigins' => ['some.where']]);

        $mockResponse = new Response();

        $mockRequest = $this->createMock(ServerRequestInterface::class);

        $mockHandler = $this->createMock(RequestHandlerInterface::class);
        $mockHandler->expects($this->once())
            ->method('handle')
            ->with($mockRequest)
            ->willReturn($mockResponse);

        $middleware = new SecurityMiddleware($mockApplicationConfig);
        $response = $middleware->process($mockRequest, $mockHandler);
        $this->assertTrue($response->hasHeader('Strict-Transport-Security'));
        $this->assertTrue($response->hasHeader('Referrer-Policy'));
        $this->assertTrue($response->hasHeader('X-Content-Type-Options'));
        $this->assertTrue($response->hasHeader('X-Permitted-Cross-Domain-Policies'));
        $this->assertTrue($response->hasHeader('X-XSS-Protection'));
    }
}
