<?php declare(strict_types=1);

namespace Reconmap;

use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Reconmap\Services\ApplicationConfig;

class CorsMiddleware implements MiddlewareInterface
{
    public function __construct(private Logger $logger,
                                private ApplicationConfig $config)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        $response = $response
            ->withHeader('Access-Control-Allow-Methods', 'GET,POST,PUT,DELETE,PATCH')
            ->withHeader('Access-Control-Allow-Headers', 'Authorization,Bulk-Operation,Content-Type');

        if ($request->hasHeader('Origin')) {
            $requestOrigin = $request->getHeaderLine('Origin');
            $corsConfig = $this->config->getSettings('cors');

            if (in_array($requestOrigin, $corsConfig['allowedOrigins'])) {
                $response = $response->withHeader('Access-Control-Allow-Origin', $requestOrigin);
            } else {
                $this->logger->warning("Invalid origin: $requestOrigin");
            }
        }

        return $response;
    }
}
