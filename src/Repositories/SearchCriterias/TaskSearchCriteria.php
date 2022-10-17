<?php declare(strict_types=1);

namespace Reconmap\Repositories\SearchCriterias;

use Ponup\SqlBuilders\SearchCriteria;

class TaskSearchCriteria extends SearchCriteria
{
    public function addProjectCriterion(int $projectId)
    {
        $this->addCriterion('t.project_id = ?', [$projectId]);
    }

    public function addStatusCriterion(string $status)
    {
        $this->addCriterion('t.status = ?', [$status]);
    }

    public function addProjectTemplateCriterion(bool $isTemplate)
    {
        $this->addCriterion('p.is_template = ?', [$isTemplate]);
    }

    public function addProjectIsNotTemplateCriterion()
    {
        $this->addProjectTemplateCriterion(false);
    }

    public function addAssigneeCriterion(int $assigneeUid)
    {
        $this->addCriterion('t.assignee_uid = ?', [$assigneeUid]);
    }

    public function addPriorityCriterion(string $priority)
    {
        $this->addCriterion('t.priority = ?', [$priority]);
    }

    public function addKeywordsCriterion(string $keywords)
    {
        $keywordsLike = "%$keywords%";

        $this->addCriterion('t.summary LIKE ? OR t.description LIKE ?', [$keywordsLike, $keywordsLike]);
    }

    public function addUserCriterion(int $userId)
    {
        $this->addCriterion('(p.visibility = "public" OR ? IN (SELECT user_id FROM project_user WHERE project_id = p.id))', [$userId]);
    }

    public function addProjectArchivedCriterion(int $projectIsArchived)
    {
        $this->addCriterion('p.archived = ?', [$projectIsArchived]);
    }
}
