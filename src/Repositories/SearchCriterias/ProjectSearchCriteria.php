<?php declare(strict_types=1);

namespace Reconmap\Repositories\SearchCriterias;

use Ponup\SqlBuilders\SearchCriteria;

class ProjectSearchCriteria extends SearchCriteria
{
    public function addTemplateCriterion(int $isTemplate)
    {
        $this->addCriterion('p.is_template = ?', [$isTemplate]);
    }

    public function addArchivedCriterion(bool $archived)
    {
        $this->addCriterion('p.archived = ?', [$archived]);
    }

    public function addIsNotTemplateCriterion()
    {
        $this->addTemplateCriterion(0);
    }

    public function addIsTemplateCriterion()
    {
        $this->addTemplateCriterion(1);
    }
}
