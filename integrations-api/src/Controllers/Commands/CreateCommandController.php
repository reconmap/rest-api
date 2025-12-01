<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use OpenApi\Attributes as OpenApi;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Http\Docs\Default201CreatedResponse;
use Reconmap\Http\Docs\Default403UnauthorisedResponse;
use Reconmap\Models\Command;
use Reconmap\Repositories\CommandRepository;

#[OpenApi\Post(
    path: "/commands",
    description: "Creates a new command",
    security: ["bearerAuth"],
    tags: ["Commands"],
)]
#[Default201CreatedResponse]
#[Default403UnauthorisedResponse]
class CreateCommandController extends Controller
{
    public function __construct(private readonly CommandRepository $repository) {}

    public function __invoke(ServerRequestInterface $request): array
    {
        $command = $this->getJsonBodyDecodedAsClass($request, new Command());
        $command->createdByUid = $request->getAttribute('userId');

        $result = $this->repository->insert($command);

        return ['success' => $result];
    }
}
