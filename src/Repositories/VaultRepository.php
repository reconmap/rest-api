<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Ponup\SqlBuilders\InsertQueryBuilder;
use Ponup\SqlBuilders\SelectQueryBuilder;
use Reconmap\Models\Vault;
use Reconmap\Repositories\SearchCriterias\VaultSearchCriteria;

class VaultRepository extends MysqlRepository
{
    private const TABLE_NAME = 'vault';

    public const UPDATABLE_COLUMNS_TYPES = [
        'name' => 's',
        'value' => 's',
        'note' => 's',
        'type' => 's',
        'reportable' => 'b',
        'record_iv' => 's',
    ];

    public function insert(Vault $vault, string $password): int
    {
        $encrypted_data = $this->encryptRecord($vault->value, $password);
        $insertStmt = new InsertQueryBuilder(self::TABLE_NAME);
        $insertStmt->setColumns('name, value, reportable, note, type, project_id, record_iv');
        $stmt = $this->db->prepare($insertStmt->toSql());
        $stmt->bind_param('ssissis', $vault->name, $encrypted_data['cipher_text'], $vault->reportable, $vault->note, $vault->type, $vault->project_id, $encrypted_data['iv']);
        return $this->executeInsertStatement($stmt);
    }

    public function deleteByIdAndProjectId(int $id, int $projectId): bool
    {
        if ($this->checkIfProjectHasVaultId($id, $projectId))
        {
            return $this->deleteByTableId(self::TABLE_NAME, $id);
        }
        return false;
    }

    protected function getBaseSelectQueryBuilder(): SelectQueryBuilder
    {
        $queryBuilder = new SelectQueryBuilder('vault v');
        $queryBuilder->setColumns('v.id, v.name, v.value, v.reportable, v.note, v.insert_ts, v.update_ts, v.type');
        return $queryBuilder;
    }

    protected function getFullSelectQueryBuilder(): SelectQueryBuilder
    {
        $queryBuilder = new SelectQueryBuilder('vault v');
        $queryBuilder->setColumns('v.id, v.name, v.value, v.reportable, v.note, v.insert_ts, v.update_ts, v.type, v.record_iv');
        return $queryBuilder;
    }

    public function search(VaultSearchCriteria $searchCriteria, bool $fullQuery = false, ?PaginationRequestHandler $paginator = null, ?string $orderBy = 'v.insert_ts DESC'): array
    {
        $queryBuilder = null;
        if ($fullQuery)
        {
            $queryBuilder = $this->getFullSelectQueryBuilder();
        }
        else
        {
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
        $vault_items = $this->search($searchCriteria);

        $exists = false;
        if ($vault_items && $vault_items[0]) {
            $exists = true;
        }
        return $exists;
    }

    private function encryptRecord(string $data, string $password): array
    {
        $cipher = 'AES-256-CTR';
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher));
        $result = [];
        $result['cipher_text'] = openssl_encrypt($data, $cipher, $password, $options=0, $iv);
        $result['iv'] = $iv;
        return $result;
    }

    private function decryptRecord(string $cipher_text, string $iv, string $password)
    {
        # TODO: for some reason, this crashes when incorrect password is used
        # based on the documentation, the openssl_decrypt should return false, but does not work
        return openssl_decrypt($cipher_text, 'AES-256-CTR', $password, $options=0, $iv);
    }

    public function findAll(int $projectId): array
    {
        $searchCriteria = new VaultSearchCriteria();
        $searchCriteria->addProjectCriterion($projectId);
        $test = $this->search($searchCriteria);
        return $test;
    }

    public function readVaultItem(int $projectId, int $vaultItemId, string $password): Vault|null
    {
        $searchCriteria = new VaultSearchCriteria();
        $searchCriteria->addVaultItemAndProjectCriterion($projectId, $vaultItemId);
        $vault_items = $this->search($searchCriteria, true);
        
        if ($vault_items && $vault_items[0]) {
            $item = new Vault();
            $item->name = $vault_items[0]['name'];
            $item->id = $vault_items[0]['id'];
            $item->insert_ts = $vault_items[0]['insert_ts'];
            $item->update_ts = $vault_items[0]['update_ts'];
            $item->reportable = (bool)($vault_items[0]['reportable']);
            $item->note = $vault_items[0]['note'];

            $decrypted = $this->decryptRecord($vault_items[0]['value'], $vault_items[0]['record_iv'], $password);
            if ($decrypted)
            {
                $item->value = $decrypted;
                return $item;
            }
        }
        return null;
    }

    public function updateVaultItemById(int $id, int $project_id, string $password, array $new_column_values): bool
    {
        $searchCriteria = new VaultSearchCriteria();
        $searchCriteria->addVaultItemAndProjectCriterion($project_id, $id);
        $vault_items = $this->search($searchCriteria, true);

        if ($vault_items && $vault_items[0]) {
            $decrypted = $this->decryptRecord($vault_items[0]['value'], $vault_items[0]['record_iv'], $password);
            if ($decrypted)
            {
                if ($new_column_values['value'])
                {
                    $encrypted_data = $this->encryptRecord($new_column_values['value'], $password);
                    $new_column_values['value'] = $encrypted_data['cipher_text'];
                    $new_column_values['record_iv'] = $encrypted_data['iv'];
                }
                return $this->updateByTableId('vault', $id, $new_column_values);
            }
        }
        return false;
    }
}
