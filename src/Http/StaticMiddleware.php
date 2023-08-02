<?php declare(strict_types=1);

namespace Reconmap\Http;

use Fig\Http\Message\StatusCodeInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Reconmap\Services\ApplicationConfig;
use Reconmap\Services\RedisServer;

class StaticMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly ApplicationConfig $applicationConfig, private readonly RedisServer $redisServer)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $cookies = $request->getCookieParams();

        if(!isset($cookies['reconmap-static']) || !$this->redisServer->exists('static-token')) {
            $response = new Response();
            return $response->withStatus(StatusCodeInterface::STATUS_FORBIDDEN);
        }

        return $handler->handle($request);
    }
}
