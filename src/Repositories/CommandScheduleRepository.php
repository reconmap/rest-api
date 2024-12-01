<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Ponup\SqlBuilders\InsertQueryBuilder;
use Ponup\SqlBuilders\SearchCriteria;
use Ponup\SqlBuilders\SelectQueryBuilder;
use Reconmap\Models\CommandSchedule;
use Reconmap\Services\PaginationRequestHandler;

class CommandScheduleRepository extends MysqlRepository implements Deletable
{
    public const array UPDATABLE_COLUMNS_TYPES = [
        'name' => 's',
        'description' => 's',
        'output_parser' => 's',
        'executable_path' => 's',
        'arguments' => 's',
        'configuration' => 's',
        'output_filename' => 's',
        'more_info_url' => 's',
        'tags' => 's'
    ];

    public function findByCommandId(int $commandId): array
    {
        $queryBuilder = $this->getBaseSelectQueryBuilder();
        $queryBuilder->setWhere('ce.command_id = ?');
        $sql = $queryBuilder->toSql();

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$commandId]);
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

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
        return new SelectQueryBuilder('command_schedule ce');
    }

    public function deleteById(int $id): bool
    {
        return $this->deleteByTableId('command_schedule', $id);
    }

    public function insert(CommandSchedule $commandSchedule): int
    {
        $insertStmt = new InsertQueryBuilder('command_schedule');
        $insertStmt->setColumns('command_id, creator_uid, argument_values, cron_expression');
        $stmt = $this->db->prepare($insertStmt->toSql());
        $stmt->bind_param('iiss', $commandSchedule->command_id, $commandSchedule->creator_uid, $commandSchedule->argument_values, $commandSchedule->cron_expression);
        return $this->executeInsertStatement($stmt);
    }

    public function updateById(int $id, array $newColumnValues): bool
    {
        return $this->updateByTableId('command', $id, $newColumnValues);
    }
}
