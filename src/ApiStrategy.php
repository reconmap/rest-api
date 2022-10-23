<?php declare(strict_types=1);

namespace Reconmap;

use Fig\Http\Message\StatusCodeInterface;
use League\Route\Http;
use League\Route\Strategy\JsonStrategy;
use Monolog\Logger;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Reconmap\Http\CorsResponseDecorator;

class ApiStrategy extends JsonStrategy
{
    public function __construct(ResponseFactoryInterface               $responseFactory,
                                private readonly CorsResponseDecorator $corsResponseDecorator,
                                private readonly Logger                $logger)
    {
        parent::__construct($responseFactory);
    }

    public function getThrowableHandler(): MiddlewareInterface
    {
        return new class ($this->responseFactory->createResponse(), $this->corsResponseDecorator, $this->logger) implements MiddlewareInterface {

            public function __construct(private readonly ResponseInterface     $response,
                                        private readonly CorsResponseDecorator $corsResponseDecorator,
                                        private readonly Logger                $logger)
            {
            }

            public function process(
                ServerRequestInterface  $request,
                RequestHandlerInterface $handler
            ): ResponseInterface
            {
                try {
                    return $handler->handle($request);
                } catch (\Throwable $exception) {
                    $this->logger->error($exception->getMessage());

                    $response = $this->response;

                    if ($exception instanceof Http\Exception) {
                        return $exception->buildJsonResponse($response);
                    }

                    $response->getBody()->write(json_encode([
                        'status_code' => StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR,
                        'reason_phrase' => 'Internal server error'
                    ]));

                    $response = $response->withAddedHeader('content-type', 'application/json');
                    $response = $response->withStatus(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR, 'Internal server error');

                    return $this->corsResponseDecorator->decorate($request, $response);
                }
            }
        };
    }
}
