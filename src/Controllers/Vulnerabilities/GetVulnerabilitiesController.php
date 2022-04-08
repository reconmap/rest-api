<?php declare(strict_types=1);

namespace Reconmap\Controllers\Vulnerabilities;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\SearchCriterias\VulnerabilitySearchCriteria;
use Reconmap\Repositories\VulnerabilityRepository;
use Reconmap\Services\PaginationRequestHandler;
use Reconmap\Services\QueryParams\OrderByRequestHandler;

class GetVulnerabilitiesController extends Controller
{
    public function __construct(
        private readonly VulnerabilityRepository $repository,
        private readonly VulnerabilitySearchCriteria $searchCriteria,
    )
    {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();

        $user = $this->getUserFromRequest($request);

        if (!$user->isAdministrator()) {
            $this->searchCriteria->addUserCriterion($user->id);
        }
        if ('client' === $user->role) {
            $this->searchCriteria->addPublicVisibilityCriterion();
        }
        if (isset($params['keywords'])) {
            $this->searchCriteria->addKeywordsCriterion($params['keywords']);
        }
        if (isset($params['projectId'])) {
            $projectId = intval($params['projectId']);
            $this->searchCriteria->addProjectCriterion($projectId);
        }
        if (isset($params['categoryId'])) {
            $categoryId = intval($params['categoryId']);
            $this->searchCriteria->addCategoryCriterion($categoryId);
        }
        if (isset($params['targetId'])) {
            $targetId = intval($params['targetId']);
            $this->searchCriteria->addTargetCriterion($targetId);
        }
        if (isset($params['isTemplate'])) {
            $isTemplate = intval($params['isTemplate']);
            $this->searchCriteria->addTemplateCriterion($isTemplate);
        }
        if (isset($params['risk'])) {
            $this->searchCriteria->addRiskCriterion($params['risk']);
        }
        if (isset($params['status'])) {
            $this->searchCriteria->addStatusCriterion($params['status']);
        }

        $orderByParser = new OrderByRequestHandler($params, 'insert_ts', validColumns: $this->repository->getSortableColumns());
        $paginator = new PaginationRequestHandler($request);
        $vulnerabilities = $this->repository->search($this->searchCriteria, $paginator, $orderByParser->toSql());
        $count = $this->repository->count($this->searchCriteria);

        $pageCount = $paginator->calculatePageCount($count);

        $response = new Response;
        $response->getBody()->write(json_encode($vulnerabilities));
        return $response
            ->withHeader('Access-Control-Expose-Headers', 'X-Total-Count,X-Page-Count')
            ->withHeader('X-Total-Count', $count)
            ->withHeader('X-Page-Count', $pageCount);
    }
}
