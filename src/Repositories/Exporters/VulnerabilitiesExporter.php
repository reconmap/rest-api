<?php declare(strict_types=1);

namespace Reconmap\Repositories\Exporters;

use Reconmap\Repositories\SearchCriterias\VulnerabilitySearchCriteria;
use Reconmap\Repositories\VulnerabilityRepository;

class VulnerabilitiesExporter implements Exportable
{
    public function __construct(private VulnerabilityRepository $repository)
    {
    }

    public function export(): array
    {
        $searchCriteria = new VulnerabilitySearchCriteria();
        $searchCriteria->addIsNotTemplateCriterion();
        return $this->repository->search($searchCriteria);
    }
}
