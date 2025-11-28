<?php declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use OpenApi\Attributes as OpenApi;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Http\Docs\Default204NoContentResponse;
use Reconmap\Http\Docs\Default403UnauthorisedResponse;
use Reconmap\Http\Docs\InPathIdParameter;
use Reconmap\Repositories\CommandUsageRepository;

#[OpenApi\Delete(path: "/commands/usage/{commandId}", description: "Deletes command usage with the given id", security: ["bearerAuth"], tags: ["Commands"], parameters: [new InPathIdParameter("commandId")])]
#[Default204NoContentResponse]
#[Default403UnauthorisedResponse]
class DeleteCommandUsageController extends Controller
{
    public function __construct(private readonly CommandUsageRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $commandId = intval($args['commandId']);

        $success = $this->repository->deleteById($commandId);

        return $success ? $this->createNoContentResponse() : $this->createInternalServerErrorResponse();
    }
}
