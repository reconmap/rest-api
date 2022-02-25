<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Ponup\SqlBuilders\InsertQueryBuilder;
use Reconmap\Models\Vault;

class VaultRepository extends MysqlRepository implements Deletable
{
    private const TABLE_NAME = 'vault';

    public function insert(Vault $vault): int
    {
        $insertStmt = new InsertQueryBuilder(self::TABLE_NAME);
        $insertStmt->setColumns('name, value, reportable, note, type, project_id');
        $stmt = $this->db->prepare($insertStmt->toSql());
        $stmt->bind_param('ssissi', $vault->name, $vault->value, $vault->reportable, $vault->note, $vault->type, $vault->project_id);
        return $this->executeInsertStatement($stmt);
    }

    public function deleteById(int $id): bool
    {
        return $this->deleteByTableId(self::TABLE_NAME, $id);
    }
}
