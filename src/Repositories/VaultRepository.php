<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Ponup\SqlBuilders\InsertQueryBuilder;
use Ponup\SqlBuilders\SelectQueryBuilder;
use Reconmap\Models\Vault;
use Reconmap\Repositories\SearchCriterias\VaultSearchCriteria;
use Reconmap\Services\PaginationRequestHandler;
use Reconmap\Services\Security\DataEncryptor;

class VaultRepository extends MysqlRepository
{
    private const string TABLE_NAME = 'vault';

    public const array UPDATABLE_COLUMNS_TYPES = [
        'type' => 's',
        'name' => 's',
        'value' => 's',
        'iv' => 's',
        'tag' => 's',
        'note' => 's',
    ];

    public function insert(Vault $vault, string $password): int
    {
        $dataEncryptor = new DataEncryptor();
        $encryptedData = $dataEncryptor->encrypt($vault->value, $password);
        $insertStmt = new InsertQueryBuilder(self::TABLE_NAME);
        $insertStmt->setColumns('name, value, tag, note, type, project_id, iv');
        $stmt = $this->mysqlServer->prepare($insertStmt->toSql());
        $stmt->bind_param('sssssis', $vault->name, $encryptedData['cipherText'], $encryptedData['tag'], $vault->note, $vault->type, $vault->project_id, $encryptedData['iv']);
        return $this->executeInsertStatement($stmt);
    }

    public function deleteByIdAndProjectId(int $id, int $projectId): bool
    {
        if ($this->checkIfProjectHasVaultId($id, $projectId)) {
            return $this->deleteByTableId(self::TABLE_NAME, $id);
        }
        return false;
    }

    protected function getBaseSelectQueryBuilder(): SelectQueryBuilder
    {
        $queryBuilder = new SelectQueryBuilder('vault v');
        $queryBuilder->setColumns('v.id, v.name, v.note, v.insert_ts, v.update_ts, v.type');
        return $queryBuilder;
    }

    protected function getFullSelectQueryBuilder(): SelectQueryBuilder
    {
        $queryBuilder = new SelectQueryBuilder('vault v');
        $queryBuilder->setColumns('v.id, v.name, v.value, v.tag, v.note, v.insert_ts, v.update_ts, v.type, v.iv');
        return $queryBuilder;
    }

    public function search(VaultSearchCriteria $searchCriteria, bool $fullQuery = false, ?PaginationRequestHandler $paginator = null, ?string $orderBy = 'v.insert_ts DESC'): array
    {
        if ($fullQuery) {
            $queryBuilder = $this->getFullSelectQueryBuilder();
        } else {
            $queryBuilder = $this->getBaseSelectQueryBuilder();

        }
        return $this->searchAll($queryBuilder, $searchCriteria, $paginator, $orderBy);
    }

    public function getVaultItemName(int $id, int $projectId): string
    {
        $searchCriteria = new VaultSearchCriteria();
        $searchCriteria->addVaultItemAndProjectCriterion($projectId, $id);
        $vault_items = $this->search($searchCriteria);

        $name = null;
        if ($vault_items && $vault_items[0]) {
            $name = $vault_items[0]['name'];
        }
        return $name;
    }

    private function checkIfProjectHasVaultId(int $id, int $projectId): bool
    {
        $searchCriteria = new VaultSearchCriteria();
        $searchCriteria->addVaultItemAndProjectCriterion($projectId, $id);
        $results = $this->search($searchCriteria);

        return $results && $results[0];
    }

    public function findAll(int $projectId): array
    {
        $searchCriteria = new VaultSearchCriteria();
        $searchCriteria->addProjectCriterion($projectId);
        return $this->search($searchCriteria);
    }

    public function readVaultItem(int $projectId, int $vaultItemId, string $password): Vault|null
    {
        $dataEncryptor = new DataEncryptor();

        $searchCriteria = new VaultSearchCriteria();
        $searchCriteria->addVaultItemAndProjectCriterion($projectId, $vaultItemId);
        $results = $this->search($searchCriteria, true);

        if ($results && $results[0]) {
            $item = new Vault();
            $item->name = $results[0]['name'];
            $item->id = $results[0]['id'];
            $item->project_id = $projectId;
            $item->insert_ts = $results[0]['insert_ts'];
            $item->update_ts = $results[0]['update_ts'];
            $item->note = $results[0]['note'];

            $decrypted = $dataEncryptor->decrypt($results[0]['value'], $results[0]['iv'], $password, $results[0]['tag']);
            if (false === $decrypted) {
                //$this->logger->warning('wrong password provided for secret');
                return null;
            }

            $item->value = $decrypted;
            return $item;
        }

        return null;
    }

    public function updateVaultItemById(int $id, int $projectId, string $password, array $newColumnValues): bool
    {
        $dataEncryptor = new DataEncryptor();

        $searchCriteria = new VaultSearchCriteria();
        $searchCriteria->addVaultItemAndProjectCriterion($projectId, $id);
        $results = $this->search($searchCriteria, true);

        if ($results && $results[0]) {
            $decrypted = $dataEncryptor->decrypt($results[0]['value'], $results[0]['iv'], $password, $results[0]['tag']);
            if (false !== $decrypted) {
                if ($newColumnValues['value']) {
                    $encryptedData = $dataEncryptor->encrypt($newColumnValues['value'], $password);
                    $newColumnValues['value'] = $encryptedData['cipherText'];
                    $newColumnValues['iv'] = $encryptedData['iv'];
                }
                return $this->updateByTableId('vault', $id, $newColumnValues);
            }
        }
        return false;
    }
}
