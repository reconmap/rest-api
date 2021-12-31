<?php declare(strict_types=1);

namespace Reconmap\Controllers\Targets;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\SearchCriterias\TargetSearchCriteria;
use Reconmap\Repositories\TargetRepository;
use Reconmap\Services\PaginationRequestHandler;

class GetTargetsController extends Controller
{
    public function __construct(private TargetRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();

        $searchCriteria = new TargetSearchCriteria();

        if (isset($params['projectId'])) {
            $searchCriteria->addProjectCriterion(intval($params['projectId']));
        }

        $paginator = new PaginationRequestHandler($request);
        $targets = $this->repository->search($searchCriteria, $paginator);
        $count = $this->repository->countSearch($searchCriteria);

        $pageCount = $paginator->calculatePageCount($count);

        $response = new Response;
        $response->getBody()->write(json_encode($targets));
        return $response
            ->withHeader('Access-Control-Expose-Headers', 'X-Page-Count')
            ->withHeader('X-Page-Count', $pageCount);

    }
}
