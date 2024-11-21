<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Ponup\SqlBuilders\InsertQueryBuilder;
use Ponup\SqlBuilders\SearchCriteria;
use Ponup\SqlBuilders\SelectQueryBuilder;
use Reconmap\Models\Command;
use Reconmap\Services\PaginationRequestHandler;

class CommandRepository extends MysqlRepository
{
    public const array UPDATABLE_COLUMNS_TYPES = [
        'name' => 's',
        'description' => 's',
        'more_info_url' => 's',
        'tags' => 's'
    ];

    public function findById(int $id): ?array
    {
        $sql = <<<SQL
SELECT
       c.*,
       u.full_name AS creator_full_name
FROM
    command c
    INNER JOIN user u ON (u.id = c.creator_uid)
WHERE c.id = ?
SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $command = $result->fetch_assoc();
        $stmt->close();

        return $command;
    }

    public function findAll(): array
    {
        $selectQueryBuilder = $this->getBaseSelectQueryBuilder();
        $sql = $selectQueryBuilder->toSql();

        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function search(SearchCriteria $searchCriteria, ?PaginationRequestHandler $paginator = null): array
    {
        $queryBuilder = $this->getBaseSelectQueryBuilder();
        $queryBuilder->setOrderBy('c.name ASC');
        return $this->searchAll($queryBuilder, $searchCriteria, $paginator);
    }

    protected function getBaseSelectQueryBuilder(): SelectQueryBuilder
    {
        return new SelectQueryBuilder('command c');
    }

    public function deleteById(int $id): bool
    {
        return $this->deleteByTableId('command', $id);
    }

    public function insert(Command|\stdClass $command): int
    {
        $insertStmt = new InsertQueryBuilder('command');
        $insertStmt->setColumns('creator_uid, name, description, more_info_url, tags');
        $stmt = $this->db->prepare($insertStmt->toSql());
        $stmt->bind_param('issss', $command->creator_uid, $command->name, $command->description, $command->more_info_url, $command->tags);
        return $this->executeInsertStatement($stmt);
    }

    public function updateById(int $id, array $newColumnValues): bool
    {
        return $this->updateByTableId('command', $id, $newColumnValues);
    }
}
