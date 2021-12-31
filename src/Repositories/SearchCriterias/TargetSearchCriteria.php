<?php declare(strict_types=1);

namespace Reconmap\Repositories\SearchCriterias;

use Ponup\SqlBuilders\SearchCriteria;

class TargetSearchCriteria extends SearchCriteria
{
    public function addProjectCriterion(int $projectId)
    {
        $this->addCriterion('t.project_id = ?', [$projectId]);
    }
}
