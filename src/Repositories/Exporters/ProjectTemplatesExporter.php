<?php declare(strict_types=1);

namespace Reconmap\Repositories\Exporters;

use Reconmap\Repositories\ProjectTemplateRepository;
use Reconmap\Repositories\SearchCriterias\ProjectSearchCriteria;

class ProjectTemplatesExporter implements Exportable
{
    public function __construct(private ProjectTemplateRepository $repository)
    {
    }

    public function export(): array
    {
        $searchCriteria = new ProjectSearchCriteria();
        return $this->repository->search($searchCriteria);
    }
}
