<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Ponup\SqlBuilders\InsertQueryBuilder;
use Ponup\SqlBuilders\SelectQueryBuilder;
use Reconmap\Models\Vault;

class VaultRepository extends MysqlRepository
{
    private const TABLE_NAME = 'vault';

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
}
