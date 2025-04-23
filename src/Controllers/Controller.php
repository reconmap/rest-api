<?php declare(strict_types=1);

namespace Reconmap\Controllers;

use GuzzleHttp\Psr7\Response;
use JsonMapper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Reconmap\DomainObjects\User;
use Reconmap\Models\Cleanable;
use Reconmap\Services\TemplateEngine;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Service\ServiceMethodsSubscriberTrait;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

abstract class Controller implements ServiceSubscriberInterface
{
    use ServiceMethodsSubscriberTrait;

    protected ?LoggerInterface $logger = null;
    protected ?TemplateEngine $template = null;

    #[Required]
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function getJsonBodyDecoded(ServerRequestInterface $request): object
    {
        return json_decode((string)$request->getBody());
    }

    public function getJsonBodyDecodedAsClass(ServerRequestInterface $request, object $instance, bool $strictNullTypes = true): object
    {
        return $this->getJsonAsClass($this->getJsonBodyDecoded($request), $instance, $strictNullTypes);
    }

    public function getJsonAsClass(array|object $json, object $instance, bool $strictNullTypes = true): object
    {
        $jsonMapper = new JsonMapper();
        $jsonMapper->bStrictNullTypes = $strictNullTypes;
        $object = $jsonMapper->map($json, $instance);
        if ($object instanceof Cleanable) {
            $object->clean();
        }
        return $object;
    }

    public function getJsonBodyDecodedAsArray(ServerRequestInterface $request): array
    {
        return json_decode((string)$request->getBody(), true);
    }

    protected function createInternalServerErrorResponse(): ResponseInterface
    {
        return new Response()->withStatus(\Symfony\Component\HttpFoundation\Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    protected function createForbiddenResponse(): ResponseInterface
    {
        return new Response()->withStatus(\Symfony\Component\HttpFoundation\Response::HTTP_FORBIDDEN);
    }

    protected function createNoContentResponse(): ResponseInterface
    {
        return new Response()->withStatus(\Symfony\Component\HttpFoundation\Response::HTTP_NO_CONTENT);
    }

    protected function createBadRequestResponse(): ResponseInterface
    {
        return new Response()->withStatus(\Symfony\Component\HttpFoundation\Response::HTTP_BAD_REQUEST);
    }

    protected function createNotFoundResponse(): ResponseInterface
    {
        return new Response()->withStatus(\Symfony\Component\HttpFoundation\Response::HTTP_NOT_FOUND);
    }

    protected function createDeletedResponse(): ResponseInterface
    {
        return $this->createNoContentResponse();
    }

    protected function createOkResponse(): ResponseInterface
    {
        return new Response()->withStatus(\Symfony\Component\HttpFoundation\Response::HTTP_OK);
    }

    protected function createStatusCreatedResponse(string|array|object $body): ResponseInterface
    {
        $jsonBody = is_string($body) ? $body : json_encode($body);

        $response = new Response()
            ->withStatus(\Symfony\Component\HttpFoundation\Response::HTTP_CREATED)
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
