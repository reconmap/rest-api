<?php declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use OpenApi\Attributes as OpenApi;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Http\Docs\Default204NoContentResponse;
use Reconmap\Http\Docs\Default403UnauthorisedResponse;
use Reconmap\Http\Docs\InPathIdParameter;
use Reconmap\Repositories\ProjectUserRepository;

#[OpenApi\Delete(path: "/projects/{projectId}/users/{userId}}", description: "Deletes project user with the given ids", security: ["bearerAuth"], tags: ["Projects"], parameters: [
    new InPathIdParameter("projectId"),
    new InPathIdParameter("userId")
])]
#[Default204NoContentResponse]
#[Default403UnauthorisedResponse]
class DeleteProjectUserController extends Controller
{
    public function __construct(private readonly ProjectUserRepository $projectUserRepository)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $membershipId = (int)$args['membershipId'];

        $success = $this->projectUserRepository->deleteById($membershipId);

        return $success ? $this->createNoContentResponse() : $this->createInternalServerErrorResponse();
    }
}
