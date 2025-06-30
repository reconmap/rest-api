<?php declare(strict_types=1);

namespace Reconmap\Controllers\Targets;

use OpenApi\Attributes as OpenApi;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Http\Docs\Default201CreatedResponse;
use Reconmap\Http\Docs\Default403UnauthorisedResponse;
use Reconmap\Models\Target;
use Reconmap\Repositories\TargetRepository;

#[OpenApi\Post(
    path: "/targets",
    description: "Creates a new asset",
    security: ["bearerAuth"],
    tags: ["Assets"],
)]
#[Default201CreatedResponse]
#[Default403UnauthorisedResponse]
class CreateTargetController extends Controller
{
    public function __construct(private TargetRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        /** @var Target $target */
        $target = $this->getJsonBodyDecodedAsClass($request, new Target());

        $targetId = $this->repository->insert($target);
        $body = ['targetId' => $targetId];

        return $this->createStatusCreatedResponse($body);
    }
}
