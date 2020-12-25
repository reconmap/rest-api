<?php

declare(strict_types=1);

namespace Reconmap\Repositories;

class TargetRepository extends MysqlRepository
{
    public function findAll(): array
    {
        $rs = $this->db->query('SELECT * FROM target LIMIT 20');
        $targets = $rs->fetch_all(MYSQLI_ASSOC);
        return $targets;
    }

    public function findById(int $id): array
    {
        $stmt = $this->db->prepare('SELECT * FROM target WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $rs = $stmt->get_result();
        $target = $rs->fetch_assoc();
        $stmt->close();

        return $target;
    }

    public function findByProjectId(int $projectId): array
    {
        $stmt = $this->db->prepare('SELECT t.*, (SELECT COUNT(*) FROM vulnerability WHERE target_id = t.id) AS num_vulnerabilities FROM target t WHERE t.project_id = ?');
        $stmt->bind_param('i', $projectId);
        $stmt->execute();
        $rs = $stmt->get_result();
        $targets = $rs->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $targets;
    }

    public function deleteById(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM target WHERE id = ?');
        $stmt->bind_param('i', $id);
        $result = $stmt->execute();
        $success = $result && 1 === $stmt->affected_rows;
        $stmt->close();

        return $success;
    }

    public function insert(int $projectId, string $name, string $kind): int
    {
        $stmt = $this->db->prepare('INSERT INTO target (project_id, name, kind) VALUES (?, ?, ?)');
        $stmt->bind_param('iss', $projectId, $name, $kind);
        return $this->executeInsertStatement($stmt);
    }
}
