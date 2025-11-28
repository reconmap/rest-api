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

    private const string PRIVATE_KEY = <<<EOD
-----BEGIN RSA PRIVATE KEY-----
MIIEowIBAAKCAQEAuzWHNM5f+amCjQztc5QTfJfzCC5J4nuW+L/aOxZ4f8J3Frew
M2c/dufrnmedsApb0By7WhaHlcqCh/ScAPyJhzkPYLae7bTVro3hok0zDITR8F6S
JGL42JAEUk+ILkPI+DONM0+3vzk6Kvfe548tu4czCuqU8BGVOlnp6IqBHhAswNMM
78pos/2z0CjPM4tbeXqSTTbNkXRboxjU29vSopcT51koWOgiTf3C7nJUoMWZHZI5
HqnIhPAG9yv8HAgNk6CMk2CadVHDo4IxjxTzTTqo1SCSH2pooJl9O8at6kkRYsrZ
WwsKlOFE2LUce7ObnXsYihStBUDoeBQlGG/BwQIDAQABAoIBAFtGaOqNKGwggn9k
6yzr6GhZ6Wt2rh1Xpq8XUz514UBhPxD7dFRLpbzCrLVpzY80LbmVGJ9+1pJozyWc
VKeCeUdNwbqkr240Oe7GTFmGjDoxU+5/HX/SJYPpC8JZ9oqgEA87iz+WQX9hVoP2
oF6EB4ckDvXmk8FMwVZW2l2/kd5mrEVbDaXKxhvUDf52iVD+sGIlTif7mBgR99/b
c3qiCnxCMmfYUnT2eh7Vv2LhCR/G9S6C3R4lA71rEyiU3KgsGfg0d82/XWXbegJW
h3QbWNtQLxTuIvLq5aAryV3PfaHlPgdgK0ft6ocU2de2FagFka3nfVEyC7IUsNTK
bq6nhAECgYEA7d/0DPOIaItl/8BWKyCuAHMss47j0wlGbBSHdJIiS55akMvnAG0M
39y22Qqfzh1at9kBFeYeFIIU82ZLF3xOcE3z6pJZ4Dyvx4BYdXH77odo9uVK9s1l
3T3BlMcqd1hvZLMS7dviyH79jZo4CXSHiKzc7pQ2YfK5eKxKqONeXuECgYEAyXlG
vonaus/YTb1IBei9HwaccnQ/1HRn6MvfDjb7JJDIBhNClGPt6xRlzBbSZ73c2QEC
6Fu9h36K/HZ2qcLd2bXiNyhIV7b6tVKk+0Psoj0dL9EbhsD1OsmE1nTPyAc9XZbb
OPYxy+dpBCUA8/1U9+uiFoCa7mIbWcSQ+39gHuECgYAz82pQfct30aH4JiBrkNqP
nJfRq05UY70uk5k1u0ikLTRoVS/hJu/d4E1Kv4hBMqYCavFSwAwnvHUo51lVCr/y
xQOVYlsgnwBg2MX4+GjmIkqpSVCC8D7j/73MaWb746OIYZervQ8dbKahi2HbpsiG
8AHcVSA/agxZr38qvWV54QKBgCD5TlDE8x18AuTGQ9FjxAAd7uD0kbXNz2vUYg9L
hFL5tyL3aAAtUrUUw4xhd9IuysRhW/53dU+FsG2dXdJu6CxHjlyEpUJl2iZu/j15
YnMzGWHIEX8+eWRDsw/+Ujtko/B7TinGcWPz3cYl4EAOiCeDUyXnqnO1btCEUU44
DJ1BAoGBAJuPD27ErTSVtId90+M4zFPNibFP50KprVdc8CR37BE7r8vuGgNYXmnI
RLnGP9p3pVgFCktORuYS2J/6t84I3+A17nEoB4xvhTLeAinAW/uTQOUmNicOP4Ek
2MsLL2kHgL8bLTmvXV4FX+PXphrDKg1XxzOYn0otuoqdAQrkK4og
-----END RSA PRIVATE KEY-----
EOD;

    public function testSuccessfulAuthentication()
    {
        $publicKey = <<<EOD
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuzWHNM5f+amCjQztc5QT
fJfzCC5J4nuW+L/aOxZ4f8J3FrewM2c/dufrnmedsApb0By7WhaHlcqCh/ScAPyJ
hzkPYLae7bTVro3hok0zDITR8F6SJGL42JAEUk+ILkPI+DONM0+3vzk6Kvfe548t
u4czCuqU8BGVOlnp6IqBHhAswNMM78pos/2z0CjPM4tbeXqSTTbNkXRboxjU29vS
opcT51koWOgiTf3C7nJUoMWZHZI5HqnIhPAG9yv8HAgNk6CMk2CadVHDo4IxjxTz
TTqo1SCSH2pooJl9O8at6kkRYsrZWwsKlOFE2LUce7ObnXsYihStBUDoeBQlGG/B
wQIDAQAB
-----END PUBLIC KEY-----
EOD;

        $config = $this->createEmptyApplicationConfig();
        $config['jwt'] = [
            'issuer' => 'http://localhost:8080/realms/reconmap',
            'audience' => 'account',
        ];

        $user = ['id' => 5, 'role' => 'superuser', 'mfa' => 'disabled'];

        $mockKeycloak = $this->createMock(KeycloakService::class);
        $mockKeycloak->expects($this->once())
            ->method('getPublicKey')
            ->willReturn($publicKey);

        $jwtPayload = new JwtPayloadCreator($config)->createFromUserArray($user);
        $jwtPayload['azp'] = 'test-client';
        $jwt = JWT::encode($jwtPayload, self::PRIVATE_KEY, 'RS256');

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
        $handler->expects($this->once())
            ->method('handle')
            ->with($request);

        $mockLogger = $this->createMock(Logger::class);

        $middleware = new AuthMiddleware($mockUserRepository, $mockKeycloak, $mockLogger, $config);
        $response = $middleware->process($request, $handler);

        $this->assertEquals(0, $response->getStatusCode());
    }

    public function testInvalidAuthenticationToken()
    {
        $publicKey = <<<EOD
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuzWHNM5f+amCjQztc5QT
fJfzCC5J4nuW+L/aOxZ4f8J3FrewM2c/dufrnmedsApb0By7WhaHlcqCh/ScAPyJ
hzkPYLae7bTVro3hok0zDITR8F6SJGL42JAEUk+ILkPI+DONM0+3vzk6Kvfe548t
u4czCuqU8BGVOlnp6IqBHhAwrswNMM78pos/2z0CjPM4tbeXqSTTbNkXRboxjU29vS
opcT51koWOgiTf3C7nJUoMWZHZI5HqnIhPAG9yv8HAgNk6CMk2CadVHDo4IxjxTz
TTqo1SCSH2pooJl9O8at6kkRYsrZWwsKlOFE2LUce7ObnXsYihStBUDoeBQlGG/B
wQIDAQAB
-----END PUBLIC KEY-----
EOD;

        $config = $this->createEmptyApplicationConfig();
        $config['jwt'] = [
            'issuer' => 'http://localhost:8080/realms/reconmap',
            'audience' => 'account',
        ];

        $user = ['id' => 5, 'role' => 'superuser', 'mfa' => 'disabled'];

        $mockKeycloak = $this->createMock(KeycloakService::class);
        $mockKeycloak->expects($this->once())
            ->method('getPublicKey')
            ->willReturn($publicKey);

        $jwtPayload = new JwtPayloadCreator($config)->createFromUserArray($user);
        $jwt = JWT::encode($jwtPayload, self::PRIVATE_KEY, 'RS256');

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

        $middleware = new AuthMiddleware($mockUserRepository, $mockKeycloak, $mockLogger, $config);
        $response = $middleware->process($request, $handler);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testForbiddenIsReturnedWhenAuthIsMissing()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('getHeader')
            ->willReturn([]);

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
