<?php

declare(strict_types=1);

namespace Reconmap\Repositories;

class ReportRepository extends MysqlRepository
{
    public function findById(int $id): array
    {
        $stmt = $this->db->prepare('SELECT * FROM report WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $rs = $stmt->get_result();
        $project = $rs->fetch_assoc();
        $stmt->close();

        return $project;
    }

    public function findAll(): array
    {
        $sql = <<<SQL
        SELECT
            r.*, p.id AS project_id, p.name AS project_name
            FROM report r
            INNER JOIN project p ON (p.id = r.project_id)
            ORDER BY r.insert_ts DESC
            LIMIT 20
        SQL;
        $rs = $this->db->query($sql);
        $projects = $rs->fetch_all(MYSQLI_ASSOC);
        return $projects;
    }

    public function insert(int $projectId, int $userId, string $format): int
    {
        $stmt = $this->db->prepare('INSERT INTO report (project_id, generated_by_uid, format) VALUES (?, ?, ?)');
        $stmt->bind_param('iis', $projectId, $userId, $format);
        return $this->executeInsertStatement($stmt);
    }

    public function deleteById(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM report WHERE id = ?');
        $stmt->bind_param('i', $id);
        $result = $stmt->execute();
        $success = $result && 1 === $stmt->affected_rows;
        $stmt->close();

        return $success;
    }
}
