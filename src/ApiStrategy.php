<?php declare(strict_types=1);

namespace Reconmap;

use GuzzleHttp\Psr7\HttpFactory;
use League\Route\Http;
use League\Route\Route;
use League\Route\Strategy\JsonStrategy;
use Monolog\Logger;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Reconmap\Http\CorsResponseDecorator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiStrategy extends JsonStrategy
{
    private HttpFactory $psr7Factory;

    public function __construct(ResponseFactoryInterface               $responseFactory,
                                private readonly CorsResponseDecorator $corsResponseDecorator,
                                private readonly Logger                $logger)
    {
        parent::__construct($responseFactory);

        $this->psr7Factory = new HttpFactory();
    }

    public function invokeRouteCallable(Route $route, ServerRequestInterface $request): ResponseInterface
    {
        $controller = $route->getCallable($this->getContainer());
        $response = $controller($request, $route->getVars());

        if ($response instanceof Response) {
            $response = $this->corsResponseDecorator->decorate($request, $this->convertSymfonyToPsr7($response));
        }
        else if ($this->isJsonSerializable($response)) {
            $body = json_encode($response, $this->jsonFlags);
            $response = $this->responseFactory->createResponse();
            $response->getBody()->write($body);
        }

        return $this->decorateResponse($response);
    }

    public function getThrowableHandler(): MiddlewareInterface
    {
        return new readonly class ($this->responseFactory->createResponse(), $this->corsResponseDecorator, $this->logger) implements MiddlewareInterface {

            public function __construct(private ResponseInterface     $response,
                                        private CorsResponseDecorator $corsResponseDecorator,
                                        private Logger                $logger)
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
                        'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                        'reason_phrase' => 'Internal server error'
                    ]));

                    $response = $response->withAddedHeader('content-type', 'application/json');
                    $response = $response->withStatus(Response::HTTP_INTERNAL_SERVER_ERROR, 'Internal server error');

                    return $this->corsResponseDecorator->decorate($request, $response);
                }
            }
        };
    }

    private function convertSymfonyToPsr7(Response $symfonyResponse): ResponseInterface
    {
        // Convert headers
        $psr7Response = $this->psr7Factory->createResponse($symfonyResponse->getStatusCode());
        foreach ($symfonyResponse->headers->all() as $name => $values) {
            foreach ($values as $value) {
                $psr7Response = $psr7Response->withAddedHeader($name, $value);
            }
        }

        // Convert body
        $psr7Response->getBody()->write($symfonyResponse->getContent());

        return $psr7Response;
    }
}
