<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\Models\Command;
use Reconmap\Repositories\QueryBuilders\InsertQueryBuilder;
use Reconmap\Repositories\QueryBuilders\SearchCriteria;
use Reconmap\Repositories\QueryBuilders\SelectQueryBuilder;
use Reconmap\Services\PaginationRequestHandler;

class CommandRepository extends MysqlRepository
{
    public const UPDATABLE_COLUMNS_TYPES = [
        'name' => 's',
        'description' => 's',
        'output_parser' => 's',
        'docker_image' => 's',
        'executable_type' => 's',
        'executable_path' => 's',
        'arguments' => 's',
        'configuration' => 's',
        'output_filename' => 's',
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

    private function getBaseSelectQueryBuilder(): SelectQueryBuilder
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
        $insertStmt->setColumns('creator_uid, name, description, docker_image, arguments, executable_type, executable_path, output_filename, more_info_url, tags, output_parser');
        $stmt = $this->db->prepare($insertStmt->toSql());
        $stmt->bind_param('issssssssss', $command->creator_uid, $command->name, $command->description, $command->docker_image, $command->arguments, $command->executable_type, $command->executable_path, $command->output_filename, $command->more_info_url, $command->tags, $command->output_parser);
        return $this->executeInsertStatement($stmt);
    }

    public function updateById(int $id, array $newColumnValues): bool
    {
        return $this->updateByTableId('command', $id, $newColumnValues);
    }
}
