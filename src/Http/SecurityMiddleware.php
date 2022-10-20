<?php declare(strict_types=1);

namespace Reconmap\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Reconmap\Services\ApplicationConfig;

class SecurityMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly ApplicationConfig $applicationConfig)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        $webClientUrl = $this->applicationConfig->getSettings('cors')['allowedOrigins'][0];

        return $response
            ->withHeader('Strict-Transport-Security', 'max-age=63072000; includeSubDomains; preload')
            ->withHeader('Referrer-Policy', 'no-referrer')
            ->withHeader('X-Content-Type-Options', 'nosniff')
            ->withHeader('Content-Security-Policy', 'frame-ancestors ' . $webClientUrl)
            ->withHeader('X-Permitted-Cross-Domain-Policies', 'none')
            ->withHeader('X-XSS-Protection', '1; mode=block');
    }
}
