<?php declare(strict_types=1);

namespace Reconmap\Controllers\Clients;

use OpenApi\Attributes as OpenApi;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Http\Docs\Default200OkResponse;
use Reconmap\Http\Docs\Default403UnauthorisedResponse;
use Reconmap\Http\Docs\InPathIdParameter;
use Reconmap\Repositories\ContactRepository;

#[OpenApi\Get(
    path: "/clients/{clientId}/contacts",
    description: "Returns all contacts for the client with the given id",
    security: ["bearerAuth"],
    tags: ["Organisations"],
    parameters: [new InPathIdParameter("clientId")])
]
#[Default200OkResponse]
#[Default403UnauthorisedResponse]
class GetClientContactsController extends Controller
{
    public function __construct(private readonly ContactRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $clientId = intval($args['clientId']);

        return $this->repository->findByClientId($clientId);
    }
}
