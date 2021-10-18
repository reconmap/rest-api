<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\Repositories\QueryBuilders\DeleteQueryBuilder;
use Reconmap\Repositories\QueryBuilders\QueryBuilder;
use Reconmap\Repositories\QueryBuilders\SearchCriteria;
use Reconmap\Repositories\QueryBuilders\SelectQueryBuilder;
use Reconmap\Repositories\QueryBuilders\UpdateQueryBuilder;
use Reconmap\Services\PaginationRequestHandler;

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

    protected function generateParamTypes(array $columnNames): string
    {
        return array_reduce($columnNames, function (string $columnTypes, string $columnName) {
            return $columnTypes . static::UPDATABLE_COLUMNS_TYPES[$columnName];
        }, '');
    }

    protected function deleteByTableId(string $tableName, int $id): bool
    {
        return 1 === $this->deleteByTableIds($tableName, [$id]);
    }

    protected function deleteByTableIds(string $tableName, array $ids): int
    {
        $successfulDeleteCount = 0;

        $deleteQueryBuilder = new DeleteQueryBuilder($tableName);
        $stmt = $this->db->prepare($deleteQueryBuilder->toSql());
        $stmt->bind_param('i', $id);
        foreach ($ids as $id) {
            $result = $stmt->execute();
            $success = $result && 1 === $stmt->affected_rows;
            $successfulDeleteCount += $success ? 1 : 0;
        }
        $stmt->close();

        return $successfulDeleteCount;
    }

    protected function updateByTableId(string $tableName, int $id, array $newColumnValues): bool
    {
        if (empty($newColumnValues)) return false;

        $updateQueryBuilder = new UpdateQueryBuilder($tableName);
        $updateQueryBuilder->setColumnValues(array_map(fn() => '?', $newColumnValues));
        $updateQueryBuilder->setWhereConditions('id = ?');

        $stmt = $this->db->prepare($updateQueryBuilder->toSql());
        $stmt->bind_param($this->generateParamTypes(array_keys($newColumnValues)) . 'i', ...array_merge(array_values($newColumnValues), [$id]));
        $result = $stmt->execute();
        $success = $result && 1 === $stmt->affected_rows;
        $stmt->close();

        return $success;
    }

    protected function searchAll(SelectQueryBuilder $queryBuilder, SearchCriteria $searchCriteria, ?PaginationRequestHandler $paginator = null, ?string $orderBy = null): array
    {
        if ($searchCriteria->hasCriteria()) {
            $criteriaSql = implode(' AND ', $searchCriteria->getCriteria());
            $queryBuilder->setWhere($criteriaSql);
        }

        if ($paginator) {
            $queryBuilder->setLimit('?, ?');
        }

        if ($orderBy) {
            $queryBuilder->setOrderBy($orderBy);
        }

        $sql = $queryBuilder->toSql();
        $stmt = $this->db->prepare($sql);

        $values = $searchCriteria->getValues();
        if ($paginator) {
            $values[] = $paginator->getLimitOffset();
            $values[] = $paginator->getLimitPerPage();
        }
        if (!empty($values)) {
            $types = array_fill(0, count($values), 's');
            $stmt->bind_param(implode('', $types), ...$values);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    protected function countSearchResults(QueryBuilder $queryBuilder, SearchCriteria $searchCriteria): int
    {
        $queryBuilder->setColumns('COUNT(*) AS total');
        $results = $this->searchAll($queryBuilder, $searchCriteria);
        return $results[0]['total'];
    }
}
