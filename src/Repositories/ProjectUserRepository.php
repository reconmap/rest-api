<?php

declare(strict_types=1);

namespace Reconmap\Repositories;

class ProjectUserRepository
{

    private $db;

    public function __construct(\mysqli $db)
    {
        $this->db = $db;
    }

    public function create(int $projectId, int $userId): bool
    {
        $stmt = $this->db->prepare('INSERT INTO project_user (project_id, user_id) VALUES (?, ?)');
        $stmt->bind_param('ii', $projectId, $userId);
        $result = $stmt->execute();
        $success = $result && 1 === $stmt->affected_rows;
        $stmt->close();

        return $success;
    }

    public function deleteById(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM project_user WHERE id = ?');
        $stmt->bind_param('i', $id);
        $result = $stmt->execute();
        $success = $result && 1 === $stmt->affected_rows;
        $stmt->close();

        return $success;
    }    
}
