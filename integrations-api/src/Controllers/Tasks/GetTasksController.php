<?php declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use OpenApi\Attributes as OpenApi;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Http\Docs\Default200OkResponse;
use Reconmap\Http\Docs\Default403UnauthorisedResponse;
use Reconmap\Repositories\SearchCriterias\TaskSearchCriteria;
use Reconmap\Repositories\TaskRepository;
use Reconmap\Services\PaginationRequestHandler;

#[OpenApi\Get(
    path: "/tasks",
    description: "Returns all tasks",
    security: ["bearerAuth"],
    tags: ["Tasks"],
)]
#[Default200OkResponse]
#[Default403UnauthorisedResponse]
class GetTasksController extends Controller
{
    public function __construct(private readonly TaskRepository     $repository,
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
            $isTemplate = filter_var($params['isTemplate'], FILTER_VALIDATE_BOOL);
            $this->searchCriteria->addProjectTemplateCriterion($isTemplate);
        } else {
            $this->searchCriteria->addProjectIsNotTemplateCriterion();
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
        if (isset($params['projectIsArchived'])) {
            $this->searchCriteria->addProjectArchivedCriterion(intval($params['projectIsArchived']));
        }

        return $this->repository->search($this->searchCriteria, $paginator);
    }
}
