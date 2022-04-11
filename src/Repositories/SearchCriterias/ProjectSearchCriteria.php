<?php declare(strict_types=1);

namespace Reconmap\Repositories\SearchCriterias;

use Ponup\SqlBuilders\SearchCriteria;

class ProjectSearchCriteria extends SearchCriteria
{
    public function addTemplateCriterion(bool $isTemplate)
    {
        $this->addCriterion('p.is_template = ?', [$isTemplate]);
    }

    public function addUserCriterion(int $userId)
    {
        $this->addCriterion('(p.visibility = "public" OR ? IN (SELECT user_id FROM project_user WHERE project_id = p.id))', [$userId]);
    }

    public function addKeywordsCriterion(string $keywords)
    {
        $keywordsLike = "%$keywords%";

        $this->addCriterion('(p.name LIKE ? OR p.description LIKE ?)', [$keywordsLike, $keywordsLike]);
    }

    public function addClientCriterion(int $clientId)
    {
        $this->addCriterion('p.client_id = ?', [$clientId]);
    }

    public function addArchivedCriterion(bool $archived)
    {
        $this->addCriterion('p.archived = ?', [$archived]);
    }

    public function addIsNotTemplateCriterion()
    {
        $this->addTemplateCriterion(false);
    }

    public function addIsTemplateCriterion()
    {
        $this->addTemplateCriterion(true);
    }
}
