<?php declare(strict_types=1);

namespace Reconmap\Controllers\Agents;

use OpenApi\Attributes as OpenApi;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Http\Docs\Default200OkResponse;
use Reconmap\Http\Docs\Default403UnauthorisedResponse;
use Reconmap\Repositories\AgentRepository;

#[OpenApi\Get(path: "/agents/ping", description: "Returns information about all agents", security: ["bearerAuth"], tags: ["Agents"])]
#[Default200OkResponse]
#[Default403UnauthorisedResponse]
class PingAgentController extends Controller
{

    public function __construct(private readonly AgentRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        return $this->repository->updateLastPingAt();
    }
}
