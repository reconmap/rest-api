<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\Models\Target;

class TargetRepository extends MysqlRepository
{
    public function findAll(): array
    {
        $rs = $this->db->query('SELECT * FROM target LIMIT 20');
        return $rs->fetch_all(MYSQLI_ASSOC);
    }

    public function findById(int $id): ?array
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

    public function findByProjectIdAndName(int $projectId, string $name): ?object
    {
        $stmt = $this->db->prepare('SELECT t.*, (SELECT COUNT(*) FROM vulnerability WHERE target_id = t.id) AS num_vulnerabilities FROM target t WHERE t.project_id = ? AND t.name = ?');
        $stmt->bind_param('is', $projectId, $name);
        $stmt->execute();
        $rs = $stmt->get_result();
        $target = $rs->fetch_object();
        $stmt->close();

        return $target;
    }

    public function deleteById(int $id): bool
    {
        return $this->deleteByTableId('target', $id);
    }

    public function insert(Target $target): int
    {
        $stmt = $this->db->prepare('INSERT INTO target (project_id, name, kind) VALUES (?, ?, ?)');
        $stmt->bind_param('iss', $target->projectId, $target->name, $target->kind);
        return $this->executeInsertStatement($stmt);
    }
}
