<?php declare(strict_types=1);

namespace Reconmap\Repositories\SearchCriterias;

use Ponup\SqlBuilders\SearchCriteria;

class VaultSearchCriteria extends SearchCriteria
{
    public function addProjectCriterion(int $projectId)
    {
        $this->addCriterion('v.project_id = ?', [$projectId]);
    }

    public function addVaultItemAndProjectCriterion(int $projectId, int $vaultItemId)
    {
        $this->addCriterion('v.id = ? AND v.project_id = ?', [$vaultItemId, $projectId]);
    }
}
