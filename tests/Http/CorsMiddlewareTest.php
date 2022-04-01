<?php declare(strict_types=1);

namespace Reconmap\Http;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CorsMiddlewareTest extends TestCase
{
    public function testBasic()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $response = new Response();

        $mockCorsResponseDecorator = $this->createMock(CorsResponseDecorator::class);
        $mockCorsResponseDecorator->expects($this->once())
            ->method('decorate')
            ->with($request, $response);

        $mockRequestHandlerInterface = $this->createMock(RequestHandlerInterface::class);
        $mockRequestHandlerInterface->expects($this->once())
            ->method('handle')
            ->willReturn($response);

        $middleware = new CorsMiddleware($mockCorsResponseDecorator);
        $middleware->process($request, $mockRequestHandlerInterface);
    }
}
