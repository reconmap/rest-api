<?php

declare(strict_types=1);

namespace Reconmap\Repositories;

class ReportRepository
{

    private $db;

    public function __construct(\mysqli $db)
    {
        $this->db = $db;
    }

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
        if (false === $stmt->execute()) {
            throw new \Exception($stmt->error);
        }
        $reportId = $stmt->insert_id;
        $stmt->close();

        return $reportId;
    }
}
