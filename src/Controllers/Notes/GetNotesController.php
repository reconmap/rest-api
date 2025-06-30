<?php declare(strict_types=1);

namespace Reconmap\Controllers\Notes;

use OpenApi\Attributes as OpenApi;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Http\Docs\Default200OkResponse;
use Reconmap\Http\Docs\Default403UnauthorisedResponse;
use Reconmap\Repositories\NoteRepository;

#[OpenApi\Get(
    path: "/notes",
    description: "Returns all notes",
    security: ["bearerAuth"],
    tags: ["Notes"],
)]
#[Default200OkResponse]
#[Default403UnauthorisedResponse]
class GetNotesController extends Controller
{
    public function __construct(private NoteRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        $params = $request->getQueryParams();
        $parentType = $params['parentType'];
        $parentId = (int)$params['parentId'];

        return $this->repository->findByParentId($parentType, $parentId);
    }
}
