<?php declare(strict_types=1);

namespace Reconmap\Repositories\Exporters;

use Ponup\SqlBuilders\SearchCriteria;
use Reconmap\Repositories\ProjectTemplateRepository;

class ProjectTemplatesExporter implements Exportable
{
    public function __construct(private ProjectTemplateRepository $repository)
    {
    }

    public function export(): array
    {
        $searchCriteria = new SearchCriteria();
        return $this->repository->search($searchCriteria);
    }
}
