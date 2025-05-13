<?php declare(strict_types=1);

namespace Reconmap\Repositories\SearchCriterias;

use Ponup\SqlBuilders\SearchCriteria;

class TaskSearchCriteria extends SearchCriteria
{
    public function addProjectCriterion(int $projectId): void
    {
        $this->addCriterion('t.project_id = ?', [$projectId]);
    }

    public function addStatusCriterion(string $status): void
    {
        $this->addCriterion('t.status = ?', [$status]);
    }

    public function addProjectTemplateCriterion(bool $isTemplate): void
    {
        $this->addCriterion('p.is_template = ?', [$isTemplate]);
    }

    public function addProjectIsNotTemplateCriterion(): void
    {
        $this->addProjectTemplateCriterion(false);
    }

    public function addAssigneeCriterion(int $assigneeUid): void
    {
        $this->addCriterion('t.assignee_uid = ?', [$assigneeUid]);
    }

    public function addPriorityCriterion(string $priority): void
    {
        $this->addCriterion('t.priority = ?', [$priority]);
    }

    public function addKeywordsCriterion(string $keywords): void
    {
        $keywordsLike = "%$keywords%";

        $this->addCriterion('t.summary LIKE ? OR t.description LIKE ?', [$keywordsLike, $keywordsLike]);
    }

    public function addUserCriterion(int $userId): void
    {
        $this->addCriterion('(p.visibility = "public" OR ? IN (SELECT user_id FROM project_user WHERE project_id = p.id))', [$userId]);
    }

    public function addProjectArchivedCriterion(int $projectIsArchived): void
    {
        $this->addCriterion('p.archived = ?', [$projectIsArchived]);
    }
}
