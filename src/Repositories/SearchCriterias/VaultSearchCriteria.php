<?php declare(strict_types=1);

namespace Reconmap\Repositories\SearchCriterias;

use Ponup\SqlBuilders\SearchCriteria;

class VaultSearchCriteria extends SearchCriteria
{
    public function addProjectCriterion(int $projectId): void
    {
        $this->addCriterion('v.project_id = ?', [$projectId]);
    }

    public function addIsProjectLessCriterion(): void
    {
        $this->addCriterion('v.project_id IS NULL');
    }

    public function addUserIdCriterion(int $userId): void
    {
        $this->addCriterion('v.owner_uid = ?', [$userId]);
    }

    public function addVaultItemAndProjectCriterion(int $projectId, int $vaultItemId): void
    {
        $this->addCriterion('v.id = ? AND v.project_id = ?', [$vaultItemId, $projectId]);
    }
}
