<?php declare(strict_types=1);

namespace Reconmap\Repositories\Exporters;

use Reconmap\Repositories\ProjectRepository;
use Reconmap\Repositories\SearchCriterias\ProjectSearchCriteria;

class ProjectsExporter implements Exportable
{
    public function __construct(private ProjectRepository $repository)
    {
    }

    public function export(string $entityType): array
    {
        $searchCriteria = new ProjectSearchCriteria();
        if ('projects' === $entityType) {
            $searchCriteria->addIsNotTemplateCriterion();
        } elseif ('project_templates' === $entityType) {
            $searchCriteria->addIsTemplateCriterion();
        }
        return $this->repository->search($searchCriteria);
    }
}
