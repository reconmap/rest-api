<?php declare(strict_types=1);

namespace Reconmap\Repositories\Exporters;

use Reconmap\Repositories\SearchCriterias\VulnerabilitySearchCriteria;
use Reconmap\Repositories\VulnerabilityRepository;

class VulnerabilitiesExporter implements Exportable
{
    public function __construct(private VulnerabilityRepository $repository)
    {
    }

    public function export(string $entityType): array
    {
        $searchCriteria = new VulnerabilitySearchCriteria();
        if ('vulnerabilities' === $entityType) {
            $searchCriteria->addIsNotTemplateCriterion();
        } elseif ('vulnerability_templates' === $entityType) {
            $searchCriteria->addIsTemplateCriterion();
        }
        return $this->repository->search($searchCriteria);
    }
}
