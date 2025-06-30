<?php declare(strict_types=1);

namespace Reconmap\Controllers\Targets;

use GuzzleHttp\Psr7\Response;
use OpenApi\Attributes as OpenApi;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Http\Docs\Default200OkResponse;
use Reconmap\Http\Docs\Default403UnauthorisedResponse;
use Reconmap\Repositories\SearchCriterias\TargetSearchCriteria;
use Reconmap\Repositories\TargetRepository;
use Reconmap\Services\PaginationRequestHandler;

#[OpenApi\Get(
    path: "/targets",
    description: "Returns all assets",
    tags: ["Assets"],
)]
#[Default200OkResponse]
#[Default403UnauthorisedResponse]
class GetTargetsController extends Controller
{
    public function __construct(private readonly TargetRepository     $repository,
                                private readonly TargetSearchCriteria $searchCriteria)
    {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();

        if (isset($params['projectId'])) {
            $this->searchCriteria->addProjectCriterion(intval($params['projectId']));
        }

        $paginateResults = isset($params['page']);
        $paginator = $paginateResults ? new PaginationRequestHandler($request) : null;

        $targets = $this->repository->search($this->searchCriteria, $paginator);

        $response = new Response;
        $response->getBody()->write(json_encode($targets));

        if ($paginateResults) {
            $count = $this->repository->countSearch($this->searchCriteria);
            $pageCount = $paginator->calculatePageCount($count);

            return $response
                ->withHeader('Access-Control-Expose-Headers', 'X-Page-Count')
                ->withHeader('X-Page-Count', $pageCount);
        }

        return $response;
    }
}
