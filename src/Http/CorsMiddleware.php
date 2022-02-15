<?php declare(strict_types=1);

namespace Reconmap\Http;

use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Reconmap\Services\ApplicationConfig;

class CorsMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly Logger $logger,
                                private readonly ApplicationConfig $config)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $corsConfig = $this->config->getSettings('cors');
        $allowedOrigins = $corsConfig['allowedOrigins'];

        $response = $handler->handle($request);

        $response = $response
            ->withHeader('Access-Control-Allow-Methods', 'GET,POST,PUT,DELETE,PATCH')
            ->withHeader('Access-Control-Allow-Headers', 'Authorization,Bulk-Operation,Content-Type');

        if (in_array('*', $allowedOrigins)) {
            $response = $response->withHeader('Access-Control-Allow-Origin', '*');
        } else if ($request->hasHeader('Origin')) {
            $requestOrigin = $request->getHeaderLine('Origin');

            if (in_array($requestOrigin, $allowedOrigins)) {
                $response = $response->withHeader('Access-Control-Allow-Origin', $requestOrigin);
            } else {
                $this->logger->warning("Invalid origin: $requestOrigin");
            }
        }

        return $response;
    }
}
