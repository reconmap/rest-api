<?php declare(strict_types=1);

namespace Reconmap\Controllers\Documents;

use OpenApi\Attributes as OpenApi;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Http\Docs\Default200OkResponse;
use Reconmap\Http\Docs\Default403UnauthorisedResponse;
use Reconmap\Repositories\DocumentRepository;

#[OpenApi\Get(
    path: "/documents",
    description: "Returns all documents",
    security: ["bearerAuth"],
    tags: ["Documents"],
)]
#[Default200OkResponse]
#[Default403UnauthorisedResponse]
class GetDocumentsController extends Controller
{
    public function __construct(private readonly DocumentRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        $params = $request->getQueryParams();
        $parentType = $params['parentType'] ?? 'library';
        $parentId = isset($params['parentId']) ? intval($params['parentId']) : null;

        return $this->repository->findByParentId($parentType, $parentId);
    }
}
