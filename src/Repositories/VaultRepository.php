<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Ponup\SqlBuilders\InsertQueryBuilder;
use Ponup\SqlBuilders\SelectQueryBuilder;
use Reconmap\Models\Vault;

class VaultRepository extends MysqlRepository
{
    private const TABLE_NAME = 'vault';

    public const UPDATABLE_COLUMNS_TYPES = [
        'name' => 's',
        'value' => 's',
        'note' => 's',
        'type' => 's',
        'reportable' => 'b'
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

    public function getVaultItemName(int $id, int $projectId): string
    {
        $queryBuilder = new SelectQueryBuilder('vault');
        $queryBuilder->setColumns('project_id, name');
        $queryBuilder->setWhere('id = ?');

        $stmt = $this->db->prepare($queryBuilder->toSql());
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        $vault_items = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $name = null;
        if ($vault_items && $vault_items[0] && ($vault_items[0]['project_id'] == $projectId)) {
            $name = $vault_items[0]['name'];
        }
        return $name;
    }

    private function checkIfProjectHasVaultId(int $id, int $projectId): bool
    {
        $queryBuilder = new SelectQueryBuilder('vault');
        $queryBuilder->setColumns('project_id');
        $queryBuilder->setWhere('id = ?');

        $stmt = $this->db->prepare($queryBuilder->toSql());
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        $vault_items = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $exists = false;
        if ($vault_items && $vault_items[0] && ($vault_items[0]['project_id'] == $projectId)) {
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
        $queryBuilder = new SelectQueryBuilder('vault');
        $queryBuilder->setColumns('id, insert_ts, update_ts, name, reportable, note, type');
        $queryBuilder->setWhere('project_id = ?');

        $stmt = $this->db->prepare($queryBuilder->toSql());
        $stmt->bind_param('i', $projectId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function readVaultItem(int $projectId, int $vaultItemId, string $password): Vault|null
    {
        $queryBuilder = new SelectQueryBuilder('vault');
        $queryBuilder->setColumns('id, insert_ts, update_ts, name, reportable, note, type, record_iv, value');
        $queryBuilder->setWhere('id = ? AND project_id = ?');

        $stmt = $this->db->prepare($queryBuilder->toSql());
        $stmt->bind_param('ii', $vaultItemId, $projectId);
        $stmt->execute();
        $result = $stmt->get_result();
        $vault_items = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
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
        $queryBuilder = new SelectQueryBuilder('vault');
        $queryBuilder->setColumns('record_iv, value');
        $queryBuilder->setWhere('id = ? AND project_id = ?');

        $stmt = $this->db->prepare($queryBuilder->toSql());
        $stmt->bind_param('ii', $id, $project_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $vault_items = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        if ($vault_items && $vault_items[0]) {
            $decrypted = $this->decryptRecord($vault_items[0]['value'], $vault_items[0]['record_iv'], $password);
            if ($decrypted)
            {
                return $this->updateByTableId('vault', $id, $new_column_values);
            }
        }
        return false;
    }
}
