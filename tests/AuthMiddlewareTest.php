<?php declare(strict_types=1);

namespace Reconmap;

use Firebase\JWT\JWT;
use League\Route\Http\Exception\ForbiddenException;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Reconmap\Http\AuthMiddleware;
use Reconmap\Services\JwtPayloadCreator;

class AuthMiddlewareTest extends TestCase
{
    use ApplicationConfigTestingTrait;

    public function testJwtTokenValidation()
    {
        $config = $this->createEmptyApplicationConfig();
        $config['jwt'] = [
            'issuer' => 'reconmap.org',
            'audience' => 'reconmap.com',
            'key' => 'this is going to be replaced with asymmetric keys'
        ];

        $user = ['id' => 5, 'role' => 'superuser', 'mfa' => 'disabled'];

        $jwtPayload = (new JwtPayloadCreator($config))->createFromUserArray($user);
        $jwt = JWT::encode($jwtPayload, $config['jwt']['key'], 'HS256');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('getHeader')
            ->willReturn(['Bearer ' . $jwt]);
        $request->expects($this->exactly(2))
            ->method('withAttribute')
            ->withConsecutive(['userId', 5], ['role', 'superuser'])
            ->willReturn($request);

        $mockUri = $this->createMock(UriInterface::class);
        $mockUri->expects($this->once())
            ->method('getPath')
            ->willReturn('/commands');
        $request->expects($this->once())
            ->method('getUri')
            ->willReturn($mockUri);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($request);

        $mockLogger = $this->createMock(Logger::class);

        /** @var AuthMiddleware */
        $middleware = new AuthMiddleware($mockLogger, $config);
        $response = $middleware->process($request, $handler);

        $this->assertNull($response->getStatusCode());
        $this->assertNull($response->getBody());
    }

    public function testForbiddenIsReturnedWhenAuthIsMissing()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('getHeader')
            ->willReturn(null);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())
            ->method('handle');

        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage("Missing 'Authorization' header");

        /** @var AuthMiddleware */
        $middleware = $this->getMockBuilder(AuthMiddleware::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();
        $middleware->process($request, $handler);
    }

    public function testForbiddenIsReturnedWhenAuthIsWrong()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('getHeader')
            ->willReturn(['Bear Polar']);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())
            ->method('handle');

        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage("Invalid 'Bearer' token");

        /** @var AuthMiddleware */
        $middleware = $this->getMockBuilder(AuthMiddleware::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();
        $middleware->process($request, $handler);
    }
}
