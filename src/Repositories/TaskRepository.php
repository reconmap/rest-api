<?php

declare(strict_types=1);

namespace Reconmap\Repositories;

class TaskRepository extends MysqlRepository
{
    public function findAll(): array
    {
        $sql = <<<SQL
SELECT
    t.id, t.project_id, t.insert_ts, t.update_ts, t.parser, t.name, t.description, t.completed,
    t.assignee_uid, u.name AS assignee_name
    FROM
    task t LEFT JOIN user u ON (u.id = t.assignee_uid)
ORDER BY
    t.insert_ts DESC
LIMIT 20
SQL;

        $rs = $this->db->query($sql);
        return $rs->fetch_all(MYSQLI_ASSOC);
    }

    public function findById(int $id): array
    {
        $stmt = $this->db->prepare('SELECT * FROM task WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $rs = $stmt->get_result();
        $project = $rs->fetch_assoc();
        $stmt->close();

        return $project;
    }

    public function findByProjectId(int $projectId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM task WHERE project_id = ?');
        $stmt->bind_param('i', $projectId);
        $stmt->execute();
        $rs = $stmt->get_result();
        $tasks = $rs->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $tasks;
    }

    public function deleteById(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM task WHERE id = ?');
        $stmt->bind_param('i', $id);
        $result = $stmt->execute();
        $success = $result && 1 === $stmt->affected_rows;
        $stmt->close();

        return $success;
    }

    public function updateById(int $id, string $column, ?string $value): bool
    {
        $stmt = $this->db->prepare('UPDATE task SET ' . $column . ' = ? WHERE id = ?');
        $stmt->bind_param('si', $value, $id);
        $result = $stmt->execute();
        $success = $result && 1 === $stmt->affected_rows;
        $stmt->close();

        return $success;
    }

    public function insert(int $projectId, string $parser, string $name, string $description): int
    {
        $stmt = $this->db->prepare('INSERT INTO task (project_id, parser, name, description) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('isss', $projectId, $parser, $name, $description);
        return $this->executeInsertStatement($stmt);
    }
}
