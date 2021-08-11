<?php declare(strict_types=1);

namespace Reconmap\Controllers\Targets;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\QueryBuilders\SearchCriteria;
use Reconmap\Repositories\TargetRepository;
use Reconmap\Services\RequestPaginator;

class GetTargetsController extends Controller
{
    public function __construct(private TargetRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();

        $searchCriteria = new SearchCriteria();

        if (isset($params['projectId'])) {
            $searchCriteria->addCriterion('t.project_id = ?', [intval($params['projectId'])]);
        }

        $paginator = new RequestPaginator($request);
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
