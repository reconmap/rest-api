<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\Models\Note;

class NoteRepository extends MysqlRepository
{
    public function findByParentId(?string $parentType, int $parentId): array
    {
        $stmt = $this->db->prepare('SELECT n.*, u.username AS user_name FROM note AS n INNER JOIN user u ON (u.id = n.user_id) WHERE n.parent_type = ? AND n.parent_id = ? ORDER BY n.insert_ts DESC');
        $stmt->bind_param('si', $parentType, $parentId);
        $stmt->execute();
        $rs = $stmt->get_result();
        $notes = $rs->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $notes;
    }

    public function deleteById(int $id): bool
    {
        return $this->deleteByTableId('note', $id);
    }

    public function insert(Note $note): int
    {
        $stmt = $this->db->prepare('INSERT INTO note (user_id, parent_type, parent_id, visibility, content) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('isiss', $note->user_id, $note->parent_type, $note->parent_id, $note->visibility, $note->content);
        return $this->executeInsertStatement($stmt);
    }
}
