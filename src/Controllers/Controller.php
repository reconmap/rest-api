<?php declare(strict_types=1);

namespace Reconmap\Controllers;

use Fig\Http\Message\StatusCodeInterface;
use GuzzleHttp\Psr7\Response;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Models\User;
use Reconmap\Services\ContainerConsumer;
use Reconmap\Services\TemplateEngine;

abstract class Controller implements ContainerConsumer
{
    protected ?Logger $logger = null;
    protected ?TemplateEngine $template = null;
    protected ?ContainerInterface $container = null;

    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }

    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    public function getJsonBodyDecoded(ServerRequestInterface $request): object
    {
        return json_decode((string)$request->getBody());
    }

    public function getJsonBodyDecodedAsClass(ServerRequestInterface $request, object $instance): object
    {
        return (new \JsonMapper())->map($this->getJsonBodyDecoded($request), $instance);
    }

    public function getJsonBodyDecodedAsArray(ServerRequestInterface $request): array
    {
        return json_decode((string)$request->getBody(), true);
    }

    protected function getIntQueryParam(array $queryParams, string $name, int $default = 0): int
    {
        return isset($queryParams[$name]) ? intval($queryParams[$name]) : $default;
    }

    protected function createStatusCreatedResponse(string|array $body): ResponseInterface
    {
        $jsonBody = is_string($body) ? $body : json_encode($body);

        $response = (new Response())
            ->withStatus(StatusCodeInterface::STATUS_CREATED)
            ->withHeader('Content-type', 'application/json');
        $response->getBody()->write($jsonBody);
        return $response;
    }

    public function getUserFromRequest(ServerRequestInterface $request): User
    {
        $user = new User();
        $user->id = $request->getAttribute('userId');
        $user->role = $request->getAttribute('role');
        return $user;
    }
}
