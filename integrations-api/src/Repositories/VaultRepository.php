<?php

declare(strict_types=1);

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
        'url' => 's',
        'expiration_date' => 's',
        'iv' => 's',
        'tag' => 's',
        'note' => 's',
    ];

    public function insert(Vault $vault, string $password): int
    {
        $dataEncryptor = new DataEncryptor();
        $encryptedData = $dataEncryptor->encrypt($vault->value, $password);
        $insertStmt = new InsertQueryBuilder(self::TABLE_NAME);
        $insertStmt->setColumns('owner_uid, name, value, tag, note, type, project_id, iv, url, expiration_date');
        $stmt = $this->mysqlServer->prepare($insertStmt->toSql());
        $stmt->bind_param('isssssisss', $vault->owner_uid, $vault->name, $encryptedData['cipherText'], $encryptedData['tag'], $vault->note, $vault->type, $vault->project_id, $encryptedData['iv'], $vault->url, $vault->expiration_date);
        return $this->executeInsertStatement($stmt);
    }

    public function deleteByIdAndUserId(int $id, int $userId): bool
    {
        return $this->deleteByTableId(self::TABLE_NAME, $id);
    }

    protected function getBaseSelectQueryBuilder(): SelectQueryBuilder
    {
        $queryBuilder = new SelectQueryBuilder('vault v');
        $queryBuilder->setColumns('v.id, v.project_id, v.name, v.note, v.created_at, v.updated_at, v.type, v.url, v.expiration_date');
        return $queryBuilder;
    }

    protected function getFullSelectQueryBuilder(): SelectQueryBuilder
    {
        $queryBuilder = new SelectQueryBuilder('vault v');
        $queryBuilder->setColumns('v.id, v.project_id, v.name, v.value, v.tag, v.note, v.created_at, v.updated_at, v.type, v.iv, v.url, v.expiration_date');
        return $queryBuilder;
    }

    public function search(VaultSearchCriteria $searchCriteria, bool $fullQuery = false, ?PaginationRequestHandler $paginator = null, ?string $orderBy = 'v.created_at DESC'): array
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

    public function findAll(int $projectId): array
    {
        $searchCriteria = new VaultSearchCriteria();
        $searchCriteria->addProjectCriterion($projectId);
        return $this->search($searchCriteria);
    }

    public function findByUserId(int $userId): array
    {
        $searchCriteria = new VaultSearchCriteria();
        $searchCriteria->addUserIdCriterion($userId);
        $searchCriteria->addIsProjectLessCriterion();
        return $this->search($searchCriteria);
    }

    public function readVaultItem(int $vaultItemId, string $password): Vault|null
    {
        $dataEncryptor = new DataEncryptor();

        $searchCriteria = new VaultSearchCriteria();
        $searchCriteria->addIdCriterion($vaultItemId);
        $results = $this->search($searchCriteria, true);

        if ($results && $results[0]) {
            $item = new Vault();
            $item->name = $results[0]['name'];
            $item->id = $results[0]['id'];
            $item->project_id = $results[0]['project_id'];;
            $item->created_at = $results[0]['created_at'];
            $item->updated_at = $results[0]['updated_at'];
            $item->note = $results[0]['note'];

            $decrypted = $dataEncryptor->decrypt($results[0]['value'], $results[0]['iv'], $password, $results[0]['tag']);
            if (false === $decrypted) {
                return null;
            }

            $item->value = $decrypted;
            return $item;
        }

        return null;
    }

    public function updateVaultItemById(int $id, string $password, array $newColumnValues): bool
    {
        $dataEncryptor = new DataEncryptor();

        $searchCriteria = new VaultSearchCriteria();
        $searchCriteria->addIdCriterion($id);
        $results = $this->search($searchCriteria, true);

        if ($results && $results[0]) {
            $decrypted = $dataEncryptor->decrypt($results[0]['value'], $results[0]['iv'], $password, $results[0]['tag']);
            if (false !== $decrypted) {
                if ($newColumnValues['value']) {
                    $encryptedData = $dataEncryptor->encrypt($newColumnValues['value'], $password);
                    $newColumnValues['value'] = $encryptedData['cipherText'];
                    $newColumnValues['iv'] = $encryptedData['iv'];
                    $newColumnValues['tag'] = $encryptedData['tag'];
                }
                return $this->updateByTableId('vault', $id, $newColumnValues);
            }
        }
        return false;
    }
}
