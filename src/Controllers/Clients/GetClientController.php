<?php declare(strict_types=1);

namespace Reconmap\Controllers\Clients;

use OpenApi\Attributes as OpenApi;
use Reconmap\Controllers\GetEntityController;
use Reconmap\Http\Docs\Default200OkResponse;
use Reconmap\Http\Docs\Default403UnauthorisedResponse;
use Reconmap\Http\Docs\InPathIdParameter;
use Reconmap\Repositories\ClientRepository;

#[OpenApi\Get(
    path: "/clients/{clientId}/contacts",
    description: "Returns information about the client with the given id",
    security: ["bearerAuth"],
    tags: ["Organisations"],
    parameters: [new InPathIdParameter("clientId")])
]
#[Default200OkResponse]
#[Default403UnauthorisedResponse]
class GetClientController extends GetEntityController
{
    public function __construct(ClientRepository $repository)
    {
        parent::__construct($repository, 'clientId');
    }
}
