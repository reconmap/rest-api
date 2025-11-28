<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Ponup\SqlBuilders\InsertQueryBuilder;
use Ponup\SqlBuilders\SearchCriteria;
use Ponup\SqlBuilders\SelectQueryBuilder;
use Reconmap\Models\CommandUsage;
use Reconmap\Services\PaginationRequestHandler;

class CommandUsageRepository extends MysqlRepository
{
    public const array UPDATABLE_COLUMNS_TYPES = [
        'command_id' => 'i',
        'name' => 's',
        'description' => 's',
        'executable_path' => 's',
        'arguments' => 's',
        'output_capturing_mode' => 's',
        'output_filename' => 's',
        'output_parser' => 's',
    ];

    public function findById(int $id): ?array
    {
        $sql = <<<SQL
SELECT
       c.*,
       u.full_name AS creator_full_name
FROM
    command_usage c
    INNER JOIN user u ON (u.id = c.creator_uid)
WHERE c.id = ?
SQL;

        $stmt = $this->mysqlServer->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $command = $result->fetch_assoc();
        $stmt->close();

        return $command;
    }

    public function findByCommandId(int $commandId): array
    {
        $queryBuilder = $this->getBaseSelectQueryBuilder();
        $queryBuilder->setWhere('cu.command_id = ?');
        $sql = $queryBuilder->toSql();

        $stmt = $this->mysqlServer->prepare($sql);
        $stmt->execute([$commandId]);
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function findAll(): array
    {
        $selectQueryBuilder = $this->getBaseSelectQueryBuilder();
        $sql = $selectQueryBuilder->toSql();

        $result = $this->mysqlServer->query($sql);
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
        return new SelectQueryBuilder('command_usage cu');
    }

    public function deleteById(int $id): bool
    {
        return $this->deleteByTableId('command_usage', $id);
    }

    public function insert(\stdClass|CommandUsage $command): int
    {
        $insertStmt = new InsertQueryBuilder('command_usage');
        $insertStmt->setColumns('creator_uid, command_id, name, description, arguments, executable_path, output_capturing_mode, output_filename, tags, output_parser');
        $stmt = $this->mysqlServer->prepare($insertStmt->toSql());
        $stmt->bind_param('iissssssss', $command->creator_uid, $command->command_id, $command->name, $command->description, $command->arguments, $command->executable_path, $command->output_capturing_mode, $command->output_filename, $command->tags, $command->output_parser);
        return $this->executeInsertStatement($stmt);
    }

    public function updateById(int $id, array $newColumnValues): bool
    {
        return $this->updateByTableId('command_usage', $id, $newColumnValues);
    }
}
