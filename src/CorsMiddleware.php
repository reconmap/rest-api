<?php declare(strict_types=1);

namespace Reconmap;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Reconmap\Services\Config;

class CorsMiddleware implements MiddlewareInterface
{
    public function __construct(private Config $config)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        return $this->decorateResponse($response);
    }

    private function decorateResponse(ResponseInterface $response): ResponseInterface
    {
        $corsConfig = $this->config->getSettings('cors');
        $allowedOrigins = implode(',', $corsConfig['allowedOrigins']);

        return $response
            ->withHeader('Access-Control-Allow-Methods', 'GET,POST,PUT,DELETE,PATCH')
            ->withHeader('Access-Control-Allow-Headers', 'Authorization,Bulk-Operation,Content-Type')
            ->withHeader('Access-Control-Allow-Origin', $allowedOrigins);
    }
}
