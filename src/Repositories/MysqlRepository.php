<?php

declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\Repositories\QueryBuilders\DeleteQueryBuilder;

abstract class MysqlRepository
{
    protected \mysqli $db;

    public function __construct(\mysqli $db)
    {
        $this->db = $db;
    }

    public function executeInsertStatement(\mysqli_stmt $stmt): int
    {
        if (false === $stmt->execute()) {
            $errorMessage = $stmt->error;
            $stmt->close();
            throw new \Exception('Unable to execute insert statement: ' . $errorMessage);
        }
        $newId = $stmt->insert_id;
        $stmt->close();

        return $newId;
    }

    protected function refValues(array $columnValues): array
    {
        $refs = [];
        foreach ($columnValues as $key => $value) {
            $refs[] = &$columnValues[$key];
        }
        return $refs;
    }

    protected function generateParamTypes(array $columnNames): string
    {
        return array_reduce($columnNames, function (string $columnTypes, string $columnName) {
            return $columnTypes . static::UPDATABLE_COLUMNS_TYPES[$columnName];
        }, '');
    }

    protected function deleteByTableId(string $tableName, int $id): bool
    {
        $deleteQueryBuilder = new DeleteQueryBuilder($tableName);
        $stmt = $this->db->prepare($deleteQueryBuilder->toSql());
        $stmt->bind_param('i', $id);
        $result = $stmt->execute();
        $success = $result && 1 === $stmt->affected_rows;
        $stmt->close();

        return $success;
    }
}
