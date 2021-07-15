<?php declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\QueryBuilders\SearchCriteria;
use Reconmap\Repositories\TaskRepository;
use Reconmap\Services\RequestPaginator;

class GetTasksController extends Controller
{
    private const PAGE_LIMIT = 20;

    public function __construct(private TaskRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        $params = $request->getQueryParams();
        $paginator = new RequestPaginator($request);

        $searchCriteria = new SearchCriteria();

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
