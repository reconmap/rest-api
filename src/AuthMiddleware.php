<?php declare(strict_types=1);

namespace Reconmap;

use Fig\Http\Message\StatusCodeInterface;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use League\Route\Http\Exception;
use League\Route\Http\Exception\ForbiddenException;
use League\Route\Http\Exception\UnauthorizedException;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Reconmap\Services\ApplicationConfig;

class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(private Logger $logger,
                                private ApplicationConfig $config)
    {
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws ForbiddenException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $jwt = $this->getToken($request);

        $jwtConfig = $this->config->getSettings('jwt');

        try {
            $token = JWT::decode($jwt, $jwtConfig['key'], ['HS256']);

            if ($token->iss !== $jwtConfig['issuer']) {
                throw new ForbiddenException("Invalid JWT issuer");
            }
            if ($token->aud !== $jwtConfig['audience']) {
                throw new ForbiddenException("Invalid JWT audience");
            }

            if (!str_contains($request->getUri()->getPath(), '/auth/mfa') && !in_array($token->data->mfa, ['verified', 'disabled'])) {
                throw new UnauthorizedException("Mfa code not verified");
            }
            $request = $request->withAttribute('userId', $token->data->id)
                ->withAttribute('role', $token->data->role);
            return $handler->handle($request);
        } catch (ForbiddenException | ExpiredException $e) {
            $this->logger->warning($e->getMessage());
            return (new Response)->withStatus(StatusCodeInterface::STATUS_UNAUTHORIZED)
                ->withBody(Utils::streamFor($e->getMessage()));
        } catch (Exception $httpException) {
            throw $httpException;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return (new Response)->withStatus(StatusCodeInterface::STATUS_BAD_REQUEST);
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @return string
     * @throws ForbiddenException
     */
    private function getToken(ServerRequestInterface $request): string
    {
        $params = $request->getQueryParams();
        if (isset($params['accessToken'])) {
            return $params['accessToken'];
        }
        $authorizationHeader = $request->getHeader('Authorization');
        if (empty($authorizationHeader)) {
            throw new ForbiddenException("Missing 'Authorization' header");
        }
        $authHeaderParts = explode(' ', $authorizationHeader[0]);
        if (count($authHeaderParts) !== 2 || strcasecmp($authHeaderParts[0], 'Bearer') !== 0) {
            throw new ForbiddenException("Invalid 'Bearer' token");
        }
        return $authHeaderParts[1];
    }
}
