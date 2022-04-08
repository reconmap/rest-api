<?php declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\SearchCriterias\TaskSearchCriteria;
use Reconmap\Repositories\TaskRepository;
use Reconmap\Services\PaginationRequestHandler;

class GetTasksController extends Controller
{
    public function __construct(private readonly TaskRepository $repository,
                                private readonly TaskSearchCriteria $searchCriteria)
    {
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        $params = $request->getQueryParams();

        $user = $this->getUserFromRequest($request);

        $paginator = new PaginationRequestHandler($request);

        if (!$user->isAdministrator()) {
            $this->searchCriteria->addUserCriterion($user->id);
        }
        if (isset($params['isTemplate'])) {
            $isTemplate = intval($params['isTemplate']);
            $this->searchCriteria->addTemplateCriterion($isTemplate);
        } else {
            $this->searchCriteria->addIsNotTemplateCriterion();
        }
        if (isset($params['keywords'])) {
            $this->searchCriteria->addKeywordsCriterion($params['keywords']);
        }
        if (isset($params['assigneeUid'])) {
            $assigneeUid = intval($params['assigneeUid']);
            $this->searchCriteria->addAssigneeCriterion($assigneeUid);
        }
        if (isset($params['projectId'])) {
            $projectId = intval($params['projectId']);
            $this->searchCriteria->addProjectCriterion($projectId);
        }
        if (isset($params['status'])) {
            $this->searchCriteria->addStatusCriterion($params['status']);
        }
        if (isset($params['priority'])) {
            $this->searchCriteria->addPriorityCriterion($params['priority']);
        }

        return $this->repository->search($this->searchCriteria, $paginator);
    }
}
