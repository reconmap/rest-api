<?php declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\ProjectRepository;
use Reconmap\Repositories\QueryBuilders\SearchCriteria;

class GetProjectsController extends Controller
{
    public function __construct(private ProjectRepository $projectRepository)
    {
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        $params = $request->getQueryParams();

        $searchCriteria = new SearchCriteria();
        if (isset($params['keywords'])) {
            $keywords = $params['keywords'];
            $keywordsLike = "%$keywords%";

            $searchCriteria->addCriterion('p.name LIKE ? OR p.description LIKE ?', [$keywordsLike, $keywordsLike]);
        }

        if (isset($params['isTemplate'])) {
            $searchCriteria->addCriterion('p.is_template = ?', [(int)$params['isTemplate']]);
        } else {
            $searchCriteria->addCriterion('p.is_template = 0');
        }

        return $this->projectRepository->search($searchCriteria);
    }
}
