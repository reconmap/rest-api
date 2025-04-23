<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Monolog\Logger;
use Ponup\SqlBuilders\DeleteQueryBuilder;
use Ponup\SqlBuilders\SearchCriteria;
use Ponup\SqlBuilders\SelectQueryBuilder;
use Ponup\SqlBuilders\UpdateQueryBuilder;
use Psr\Log\LoggerInterface;
use Reconmap\Database\MysqlServer;
use Reconmap\Services\PaginationRequestHandler;

abstract class MysqlRepository
{
    protected ?Logger $logger = null;

    public function __construct(protected readonly MysqlServer $mysqlServer)
    {
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
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
        $stmt = $this->mysqlServer->prepare($deleteQueryBuilder->toSql());
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
        if (empty($newColumnValues)) {
            return false;
        }

        $updateQueryBuilder = new UpdateQueryBuilder($tableName);
        $updateQueryBuilder->setColumnValues(array_map(fn() => '?', $newColumnValues));
        $updateQueryBuilder->setWhereConditions('id = ?');

        $stmt = $this->mysqlServer->prepare($updateQueryBuilder->toSql());
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
        $stmt = $this->mysqlServer->prepare($sql);

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

    protected function countSearchResults(SelectQueryBuilder $queryBuilder, SearchCriteria $searchCriteria): int
    {
        $queryBuilder->setColumns('COUNT(*) AS total');
        $results = $this->searchAll($queryBuilder, $searchCriteria);
        return $results[0]['total'];
    }

    protected function getBaseSelectQueryBuilder(): SelectQueryBuilder
    {
        throw new \BadMethodCallException("Unimplemented");
    }

    public function count(SearchCriteria $searchCriteria): int
    {
        $queryBuilder = $this->getBaseSelectQueryBuilder();
        return $this->countSearchResults($queryBuilder, $searchCriteria);
    }
}
