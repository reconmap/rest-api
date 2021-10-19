<?php declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\SearchCriterias\TaskSearchCriteria;
use Reconmap\Repositories\TaskRepository;

class GetProjectTasksController extends Controller
{
    public function __construct(private TaskRepository $taskRepository, private TaskSearchCriteria $searchCriteria)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $projectId = (int)$args['projectId'];

        $this->searchCriteria->addProjectCriterion($projectId);

        return $this->taskRepository->search($this->searchCriteria);
    }
}
