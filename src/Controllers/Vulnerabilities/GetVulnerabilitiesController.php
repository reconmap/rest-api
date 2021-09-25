<?php declare(strict_types=1);

namespace Reconmap\Controllers\Vulnerabilities;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\SearchCriterias\VulnerabilitySearchCriteria;
use Reconmap\Repositories\VulnerabilityRepository;
use Reconmap\Repositories\VulnerabilityStatsRepository;
use Reconmap\Services\PaginationRequestHandler;
use Reconmap\Services\QueryParams\OrderByRequestHandler;

class GetVulnerabilitiesController extends Controller
{
    public function __construct(
        private VulnerabilityRepository      $repository,
        private VulnerabilitySearchCriteria  $searchCriteria,
        private VulnerabilityStatsRepository $statsRepository
    )
    {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();

        if (isset($params['keywords'])) {
            $this->searchCriteria->addKeywordsCriterion($params['keywords']);
        }
        if (isset($params['projectId'])) {
            $projectId = intval($params['projectId']);
            $this->searchCriteria->addProjectCriterion($projectId);
        }
        if (isset($params['targetId'])) {
            $targetId = intval($params['targetId']);
            $this->searchCriteria->addTargetCriterion($targetId);
        }
        if (isset($params['isTemplate'])) {
            $isTemplate = intval($params['isTemplate']);
            $this->searchCriteria->addTemplateCriterion($isTemplate);
        }

        $role = $request->getAttribute('role');
        if ('client' === $role) {
            $this->searchCriteria->addPublicVisibilityCriterion();
        }

        $orderByParser = new OrderByRequestHandler($params, 'insert_ts', validColumns: $this->repository->getSortableColumns());
        $paginator = new PaginationRequestHandler($request);
        $vulnerabilities = $this->repository->search($this->searchCriteria, $paginator, $orderByParser->toSql());

        $count = $this->statsRepository->countAll();
        $pageCount = $paginator->calculatePageCount($count);

        $response = new Response;
        $response->getBody()->write(json_encode($vulnerabilities));
        return $response
            ->withHeader('Access-Control-Expose-Headers', 'X-Page-Count')
            ->withHeader('X-Page-Count', $pageCount);
    }
}
