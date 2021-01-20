<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\Models\CommandOutput;

class CommandOutputRepository extends MysqlRepository
{
    public function insert(CommandOutput $commandOutput): int
    {
        $stmt = $this->db->prepare('INSERT INTO command_output (command_id, submitted_by_uid, file_name, file_content, file_size, file_mimetype) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('iissis', $commandOutput->command_id, $commandOutput->submitted_by_uid, $commandOutput->file_name, $commandOutput->file_content, $commandOutput->file_size, $commandOutput->file_mimetype);
        return $this->executeInsertStatement($stmt);
    }

    public function findByTaskId(int $taskId): array
    {
        $sql = <<<SQL
SELECT
    co.*,
    u.full_name AS submitter_name
FROM
    command_output co
    INNER JOIN task t ON (t.id = ?)
    INNER JOIN user u ON (u.id = co.submitted_by_uid)
    WHERE co.command_id = t.command_id
SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $taskId);
        $stmt->execute();
        $rs = $stmt->get_result();
        $results = $rs->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $results;
    }

    public function deleteById(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM command_output WHERE id = ?');
        $stmt->bind_param('i', $id);
        $result = $stmt->execute();
        $success = $result && 1 === $stmt->affected_rows;
        $stmt->close();

        return $success;
    }
}
