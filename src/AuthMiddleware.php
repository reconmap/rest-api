<?php declare(strict_types=1);

namespace Reconmap;

use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use GuzzleHttp\Psr7\Response;
use League\Route\Http\Exception\BadRequestException;
use League\Route\Http\Exception\ForbiddenException;
use League\Route\Http\Exception\UnauthorizedException;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Reconmap\Services\Config;
use Reconmap\Services\ConfigConsumer;

class AuthMiddleware implements MiddlewareInterface, ConfigConsumer
{
    private Logger $logger;
    private Config $config;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param Config $config
     */
    public function setConfig(Config $config): void
    {
        $this->config = $config;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws ForbiddenException
     * @throws UnauthorizedException
     * @throws BadRequestException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $authorizationHeader = $request->getHeader('Authorization');
        if (empty($authorizationHeader)) {
            throw new ForbiddenException("Missing 'Authorization' header");
        }
        $authHeaderParts = explode(' ', $authorizationHeader[0]);
        if (count($authHeaderParts) !== 2 || strcasecmp($authHeaderParts[0], 'Bearer') !== 0) {
            throw new ForbiddenException("Invalid 'Bearer' token");
        }
        $jwt = $authHeaderParts[1];

        $jwtConfig = $this->config->getSettings('jwt');

        try {
            $token = JWT::decode($jwt, $jwtConfig['key'], ['HS256']);

            if ($token->iss !== $jwtConfig['issuer']) {
                throw new ForbiddenException("Invalid JWT issuer");
            }
            if ($token->aud !== $jwtConfig['audience']) {
                throw new ForbiddenException("Invalid JWT audience");
            }

            $request = $request->withAttribute('userId', $token->data->id);
            return $handler->handle($request);
        } catch (ForbiddenException | ExpiredException $e) {
            $this->logger->warning($e->getMessage());
            return (new Response)->withStatus(401);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return (new Response)->withStatus(400);
        }
    }
}
