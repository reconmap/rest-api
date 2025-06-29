<?php declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use OpenApi\Attributes as OpenApi;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Http\Docs\Default204NoContentResponse;
use Reconmap\Http\Docs\Default403UnauthorisedResponse;
use Reconmap\Http\Docs\InPathIdParameter;
use Reconmap\Repositories\CommandRepository;

#[OpenApi\Delete(path: "/commands/{commandId}", description: "Deletes command with the given id", security: ["bearerAuth"], tags: ["Commands"], parameters: [new InPathIdParameter("commandId")])]
#[Default204NoContentResponse]
#[Default403UnauthorisedResponse]
class DeleteCommandController extends Controller
{
    public function __construct(private readonly CommandRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $commandId = intval($args['commandId']);

        $success = $this->repository->deleteById($commandId);

        return $success ? $this->createNoContentResponse() : $this->createInternalServerErrorResponse();
    }
}
