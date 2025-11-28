<?php

declare(strict_types=1);

namespace Reconmap\Http;

use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use League\Route\Http\Exception;
use League\Route\Http\Exception\ForbiddenException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Reconmap\Repositories\UserRepository;
use Reconmap\Services\ApplicationConfig;
use Reconmap\Services\KeycloakService;

readonly class AuthMiddleware implements MiddlewareInterface
{
    private const array AUTHORIZED_PARTIES = ['reconmapd-client'];

    public function __construct(
        private UserRepository    $userRepository,
        private KeycloakService   $keycloak,
        private LoggerInterface   $logger,
        private ApplicationConfig $config
    )
    {
    }

    /**
     * @throws ForbiddenException|Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $jwt = $this->getToken($request);

        $jwtConfig = $this->config->getSettings('jwt');

        try {
            $token = JWT::decode($jwt, new Key($this->keycloak->getPublicKey(), 'RS256'));

            if ($token->iss !== $jwtConfig['issuer']) {
                throw new ForbiddenException("Invalid JWT issuer: " . $token->iss);
            }

            if (isset($token->azp) && in_array($token->azp, self::AUTHORIZED_PARTIES)) {
                $request = $request->withAttribute('userId', 0)
                    ->withAttribute('role', 'administrator'); // api

            } elseif (in_array($token->azp, ['web-client', 'rmap-client'])) {
                $dbUser = $this->userRepository->findBySubjectId($token->sub);
                if (is_null($dbUser)) {
                    $this->logger->error('No user with subject: ' . $token->sub);
                }

                if (!in_array($jwtConfig['audience'], is_array($token->aud) ? $token->aud : [$token->aud])) {
                    $this->logger->warning("Invalid JWT audience: " . var_export($token->aud, true));
                }

                $tokenRole = $this->extractRoleFromToken($token);
                $request = $request->withAttribute('userId', $dbUser['id'])
                    ->withAttribute('role', $tokenRole)
                    ->withAttribute('token', $jwt);
            }
            return $handler->handle($request);
        } catch (ForbiddenException|ExpiredException $e) {
            $this->logger->warning($e->getMessage());
            return (new Response)->withStatus(\Symfony\Component\HttpFoundation\Response::HTTP_UNAUTHORIZED)
                ->withBody(Utils::streamFor($e->getMessage()));
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return (new Response)->withStatus(\Symfony\Component\HttpFoundation\Response::HTTP_BAD_REQUEST);
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

    private function extractRoleFromToken(object $token): string
    {
        if (isset($token->resource_access->{'web-client'}->roles[0])) {
            return $token->resource_access->{'web-client'}->roles[0];
        }

        return $token->realm_access->resource_access->{'web-client'}->roles[0];
    }
}
