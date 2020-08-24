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

    public function findAll(): array
    {
        $sql = <<<SQL
        SELECT
            r.*, p.id AS project_id, p.name AS project_name
            FROM report r
            INNER JOIN project p ON (p.id = r.project_id)
            ORDER BY r.insert_ts DESC
        SQL;
        $rs = $this->db->query($sql);
        $projects = $rs->fetch_all(MYSQLI_ASSOC);
        return $projects;
    }

    public function insert(int $projectId, int $userId, string $format): void
    {
        $stmt = $this->db->prepare('INSERT INTO report (project_id, generated_by_uid, format) VALUES (?, ?, ?)');
        $stmt->bind_param('iis', $projectId, $userId, $format);
        if (false === $stmt->execute()) {
            throw new \Exception($stmt->error);
        }
        $stmt->close();
    }
}
