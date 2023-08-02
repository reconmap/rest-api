<?php declare(strict_types=1);

namespace Reconmap\Http;

use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Services\ApplicationConfig;

class CorsResponseDecorator
{
    public function __construct(private readonly Logger $logger,
                                private readonly ApplicationConfig $config)
    {
    }

    public function decorate(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $corsConfig = $this->config->getSettings('cors');
        $allowedOrigins = $corsConfig['allowedOrigins'];

        $response = $response
            ->withHeader('Access-Control-Allow-Methods', 'GET,POST,PUT,DELETE,PATCH')
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('Access-Control-Allow-Headers', 'Authorization,Bulk-Operation,Content-Type,Set-Cookie');

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
