<?php declare(strict_types=1);

namespace Reconmap\Repositories;

class ProjectUserRepository extends MysqlRepository
{
    public function create(int $projectId, int $userId): int
    {
        $stmt = $this->mysqlServer->prepare('INSERT INTO project_user (project_id, user_id) VALUES (?, ?)');
        $stmt->bind_param('ii', $projectId, $userId);
        return $this->executeInsertStatement($stmt);
    }

    public function deleteById(int $id): bool
    {
        $stmt = $this->mysqlServer->prepare('DELETE FROM project_user WHERE id = ?');
        $stmt->bind_param('i', $id);
        $result = $stmt->execute();
        $success = $result && 1 === $stmt->affected_rows;
        $stmt->close();

        return $success;
    }
}
