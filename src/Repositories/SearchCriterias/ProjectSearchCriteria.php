<?php declare(strict_types=1);

namespace Reconmap\Repositories\SearchCriterias;

use Reconmap\Repositories\QueryBuilders\SearchCriteria;

class ProjectSearchCriteria extends SearchCriteria
{
    public function addTemplateCriterion(int $isTemplate)
    {
        $this->addCriterion('p.is_template = ?', [$isTemplate]);
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
