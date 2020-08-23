<?php

declare(strict_types=1);

namespace Reconmap;

use Firebase\JWT\JWT;
use League\Route\Http\Exception\ForbiddenException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthMiddleware implements MiddlewareInterface
{

    // @todo replace with RSA keys
    const JWT_KEY = 'this is going to be replaced with asymmetric keys';

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $jwt = explode(' ', $request->getHeader('Authorization')[0])[1];

        try {
            $token = JWT::decode($jwt, self::JWT_KEY, ['HS256']);

            if ($token->iss !== 'reconmap.org') {
                throw new ForbiddenException();
            }
            if ($token->aud !== 'reconmap.com') {
                throw new ForbiddenException();
            }

            $request = $request->withAttribute('userId', $token->data->id);
            $response = $handler->handle($request);
            return $response;
        } catch (\Exception $e) {
            return (new \GuzzleHttp\Psr7\Response)
                ->withStatus(401)
                ->withHeader('Access-Control-Allow-Methods', 'GET,POST,PUT,DELETE')
                ->withHeader('Access-Control-Allow-Headers', 'Authorization')
                ->withHeader('Access-Control-Allow-Origin', '*');
        }
    }
}
