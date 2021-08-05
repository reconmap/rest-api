<?php declare(strict_types=1);

namespace Reconmap\Controllers\Vulnerabilities;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\QueryBuilders\SearchCriteria;
use Reconmap\Repositories\VulnerabilityRepository;
use Reconmap\Repositories\VulnerabilityStatsRepository;
use Reconmap\Services\RequestPaginator;

class GetVulnerabilitiesController extends Controller
{
    public function __construct(
        private VulnerabilityRepository      $repository,
        private VulnerabilityStatsRepository $statsRepository
    )
    {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();

        $searchCriteria = new SearchCriteria();

        if (isset($params['keywords'])) {
            $keywords = $params['keywords'];
            $keywordsLike = "%$keywords%";

            $searchCriteria->addCriterion('(v.summary LIKE ? OR v.description LIKE ?)', [$keywordsLike, $keywordsLike]);
        }
        if (isset($params['targetId'])) {
            $targetId = (int)$params['targetId'];
            $searchCriteria->addCriterion('v.target_id = ?', [$targetId]);
        }
        if (isset($params['isTemplate'])) {
            $searchCriteria->addCriterion('v.is_template = ?', [intval($params['isTemplate'])]);
        }

        $paginator = new RequestPaginator($request);
        $vulnerabilities = $this->repository->search($searchCriteria, $paginator);

        $count = $this->statsRepository->countAll();
        $pageCount = $paginator->calculatePageCount($count);

        $response = new Response;
        $response->getBody()->write(json_encode($vulnerabilities));
        return $response
            ->withHeader('Access-Control-Expose-Headers', 'X-Page-Count')
            ->withHeader('X-Page-Count', $pageCount);
    }
}
