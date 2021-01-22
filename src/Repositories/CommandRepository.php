<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\Repositories\QueryBuilders\SelectQueryBuilder;

class CommandRepository extends MysqlRepository
{
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM command WHERE id = ?');
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
        $stmt = $this->db->prepare('INSERT INTO command (creator_uid, short_name, description, docker_image, container_args) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('issss', $command->creator_uid, $command->short_name, $command->description, $command->docker_image, $command->container_args);
        return $this->executeInsertStatement($stmt);
    }
}
