<?php

declare(strict_types=1);

namespace Reconmap\Repositories;

class ClientRepository extends MysqlRepository
{
    public function findAll(): array
    {
        $rs = $this->db->query('SELECT * FROM client LIMIT 20');
        $targets = $rs->fetch_all(MYSQLI_ASSOC);
        return $targets;
    }

    public function findById(int $id): array
    {
        $stmt = $this->db->prepare('SELECT * FROM client WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $rs = $stmt->get_result();
        $target = $rs->fetch_assoc();
        $stmt->close();

        return $target;
    }

    public function deleteById(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM client WHERE id = ?');
        $stmt->bind_param('i', $id);
        $result = $stmt->execute();
        $success = $result && 1 === $stmt->affected_rows;
        $stmt->close();

        return $success;
    }

    public function insert(int $projectId, string $name, string $kind): int
    {
        $stmt = $this->db->prepare('INSERT INTO client (project_id, name, kind) VALUES (?, ?, ?)');
        $stmt->bind_param('iss', $projectId, $name, $kind);
        return $this->executeInsertStatement($stmt);
    }
}
