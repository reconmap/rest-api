<?php

declare(strict_types=1);

namespace Reconmap\Repositories;

use Exception;

class TaskResultRepository
{

    private $db;

    public function __construct(\mysqli $db)
    {
        $this->db = $db;
    }

    public function findAll(): array
    {
        $rs = $this->db->query('SELECT * FROM task');
        $projects = $rs->fetch_all(MYSQLI_ASSOC);
        return $projects;
    }

    public function insert(int $taskId, int $userId, string $output): bool
    {
        $stmt = $this->db->prepare('INSERT INTO task_result (task_id, submitted_by_uid, output) VALUES (?, ?, ?)');
        $stmt->bind_param('iis', $taskId, $userId, $output);
        $result = $stmt->execute();
        if(!$result) throw new Exception($stmt->error);
        $success = $result && 1 === $stmt->affected_rows;
        $stmt->close();

        return $success;
    }
}
