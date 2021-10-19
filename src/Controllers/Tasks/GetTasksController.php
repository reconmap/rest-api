<?php declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use Ponup\SqlBuilders\SearchCriteria;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\TaskRepository;
use Reconmap\Services\PaginationRequestHandler;

class GetTasksController extends Controller
{
    public function __construct(private TaskRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        $params = $request->getQueryParams();
        $paginator = new PaginationRequestHandler($request);

        $searchCriteria = new SearchCriteria();

        if (isset($params['isTemplate'])) {
            $searchCriteria->addCriterion('p.is_template = ?', [(int)$params['isTemplate']]);
        } else {
            $searchCriteria->addCriterion('p.is_template = 0');
        }

        if (isset($params['keywords'])) {
            $keywords = $params['keywords'];
            $keywordsLike = "%$keywords%";

            $searchCriteria->addCriterion('t.summary LIKE ? OR t.description LIKE ?', [$keywordsLike, $keywordsLike]);
        }
        if (isset($params['assigneeUid'])) {
            $assigneeUid = intval($params['assigneeUid']);

            $searchCriteria->addCriterion('t.assignee_uid = ?', [$assigneeUid]);
        }

        return $this->repository->search($searchCriteria, $paginator);
    }
}
