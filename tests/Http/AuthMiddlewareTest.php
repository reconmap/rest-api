<?php declare(strict_types=1);

namespace Reconmap\Http;

use Firebase\JWT\JWT;
use League\Route\Http\Exception\ForbiddenException;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Reconmap\ApplicationConfigTestingTrait;
use Reconmap\ConsecutiveParamsTrait;
use Reconmap\Repositories\UserRepository;
use Reconmap\Services\JwtPayloadCreator;
use Reconmap\Services\KeycloakService;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddlewareTest extends TestCase
{
    use ApplicationConfigTestingTrait;
    use ConsecutiveParamsTrait;

    public function testJwtTokenValidation()
    {
        $config = $this->createEmptyApplicationConfig();
        $config['jwt'] = [
            'issuer' => 'reconmap.org',
            'audience' => 'reconmap.com',
            'key' => 'this is going to be replaced with asymmetric keys'
        ];

        $user = ['id' => 5, 'role' => 'superuser', 'mfa' => 'disabled'];

        $mockKeycloak = $this->createMock(KeycloakService::class);
        $mockKeycloak->expects($this->once())
            ->method('getPublicKey')
            ->willReturn('xxx');

        $jwtPayload = (new JwtPayloadCreator($config))->createFromUserArray($user);
        $jwt = JWT::encode($jwtPayload, $config['jwt']['key'], 'HS256');

        $mockUserRepository = $this->createMock(UserRepository::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('getHeader')
            ->willReturn(['Bearer ' . $jwt]);
        $request->expects($this->exactly(0))
            ->method('withAttribute')
            ->with(...$this->consecutiveParams(['userId', 5], ['role', 'superuser']))
            ->willReturn($request);

        $mockUri = $this->createMock(UriInterface::class);
        $mockUri->expects($this->never())
            ->method('getPath')
            ->willReturn('/commands');
        $request->expects($this->never())
            ->method('getUri')
            ->willReturn($mockUri);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())
            ->method('handle')
            ->with($request);

        $mockLogger = $this->createMock(Logger::class);

        /** @var AuthMiddleware */
        $middleware = new AuthMiddleware($mockUserRepository, $mockKeycloak, $mockLogger, $config);
        $response = $middleware->process($request, $handler);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
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
