<?php declare(strict_types=1);

namespace Reconmap\Repositories\SearchCriterias;

use Reconmap\Repositories\QueryBuilders\SearchCriteria;

class TaskSearchCriteria extends SearchCriteria
{
    public function addProjectCriterion(int $projectId)
    {
        $this->addCriterion('t.project_id = ?', [$projectId]);
    }
}
