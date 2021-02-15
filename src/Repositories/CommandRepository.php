<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\Repositories\QueryBuilders\SelectQueryBuilder;
use Reconmap\Repositories\QueryBuilders\UpdateQueryBuilder;

class CommandRepository extends MysqlRepository
{
    public const UPDATABLE_COLUMNS_TYPES = [
        'short_name' => 's',
        'description' => 's',
        'docker_image' => 's',
        'executable_type' => 's',
        'executable_path' => 's',
        'arguments' => 's',
        'configuration' => 's'
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
        $rs = $stmt->get_result();
        $command = $rs->fetch_assoc();
        $stmt->close();

        return $command;
    }

    public function findAll(): array
    {
        $selectQueryBuilder = $this->getBaseSelectQueryBuilder();
        $sql = $selectQueryBuilder->toSql();

        $rs = $this->db->query($sql);
        return $rs->fetch_all(MYSQLI_ASSOC);
    }

    private function getBaseSelectQueryBuilder(): SelectQueryBuilder
    {
        $queryBuilder = new SelectQueryBuilder('command c');
        return $queryBuilder;
    }

    public function deleteById(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM command WHERE id = ?');
        $stmt->bind_param('i', $id);
        $result = $stmt->execute();
        $success = $result && 1 === $stmt->affected_rows;
        $stmt->close();

        return $success;
    }

    public function insert(object $command): int
    {
        $stmt = $this->db->prepare('INSERT INTO command (creator_uid, short_name, description, docker_image, arguments, executable_type, executable_path) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('issssss', $command->creator_uid, $command->short_name, $command->description, $command->docker_image, $command->arguments, $command->executable_type, $command->executable_path);
        return $this->executeInsertStatement($stmt);
    }

    public function updateById(int $id, array $newColumnValues): bool
    {
        $updateQueryBuilder = new UpdateQueryBuilder('command');
        $updateQueryBuilder->setColumnValues(array_map(fn() => '?', $newColumnValues));
        $updateQueryBuilder->setWhereConditions('id = ?');

        $stmt = $this->db->prepare($updateQueryBuilder->toSql());
        call_user_func_array([$stmt, 'bind_param'], [$this->generateParamTypes(array_keys($newColumnValues)) . 'i', ...$this->refValues($newColumnValues), &$id]);
        $result = $stmt->execute();
        $success = $result && 1 === $stmt->affected_rows;
        $stmt->close();

        return $success;
    }
}
