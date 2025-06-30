<?php declare(strict_types=1);

namespace Reconmap\Controllers\Targets;

use OpenApi\Attributes as OpenApi;
use Reconmap\Controllers\GetEntityController;
use Reconmap\Http\Docs\Default200OkResponse;
use Reconmap\Http\Docs\Default403UnauthorisedResponse;
use Reconmap\Http\Docs\InPathIdParameter;
use Reconmap\Repositories\TargetRepository;

#[OpenApi\Get(
    path: "/targets/{assetId}",
    description: "Returns information about the asset with the given id",
    security: ["bearerAuth"],
    tags: ["Assets"],
    parameters: [new InPathIdParameter("assetId")])
]
#[Default200OkResponse]
#[Default403UnauthorisedResponse]
class GetTargetController extends GetEntityController
{
    public function __construct(TargetRepository $repository)
    {
        parent::__construct($repository, 'targetId');
    }
}
