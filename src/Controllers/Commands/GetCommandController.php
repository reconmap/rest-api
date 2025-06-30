<?php declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use League\Route\Http\Exception\NotFoundException;
use OpenApi\Attributes as OpenApi;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Http\Docs\Default200OkResponse;
use Reconmap\Http\Docs\Default403UnauthorisedResponse;
use Reconmap\Http\Docs\InPathIdParameter;
use Reconmap\Repositories\CommandRepository;

#[OpenApi\Get(
    path: "/commands/{commandId}",
    description: "Returns information about the command with the given id",
    security: ["bearerAuth"],
    tags: ["Commands"],
    parameters: [new InPathIdParameter("commandId")])
]
#[Default200OkResponse]
#[Default403UnauthorisedResponse]
class GetCommandController extends Controller
{
    public function __construct(private readonly CommandRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $commandId = (int)$args['commandId'];

        $command = $this->repository->findById($commandId);
        if (is_null($command)) {
            throw new NotFoundException();
        }

        return $command;
    }
}
