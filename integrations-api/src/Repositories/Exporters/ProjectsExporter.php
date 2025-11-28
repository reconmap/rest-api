<?php declare(strict_types=1);

namespace Reconmap\Repositories\Exporters;

use Reconmap\Repositories\ProjectRepository;
use Reconmap\Repositories\SearchCriterias\ProjectSearchCriteria;

class ProjectsExporter implements Exportable
{
    public function __construct(private ProjectRepository $repository)
    {
    }

    public function export(): array
    {
        $searchCriteria = new ProjectSearchCriteria();
        $searchCriteria->addIsNotTemplateCriterion();
        return $this->repository->search($searchCriteria);
    }
}
