<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\Models\Attachment;

class AttachmentRepository extends MysqlRepository
{
    public function insert(Attachment $attachment): int
    {
        $stmt = $this->db->prepare('INSERT INTO attachment (parent_type, parent_id, submitter_uid, client_file_name, file_name, file_hash, file_size, file_mimetype) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('siisssis', $attachment->parent_type, $attachment->parent_id, $attachment->submitter_uid, $attachment->client_file_name, $attachment->file_name, $attachment->file_hash, $attachment->file_size, $attachment->file_mimetype);
        return $this->executeInsertStatement($stmt);
    }

    public function findById(int $attachmentId): ?Attachment
    {
        $sql = <<<SQL
SELECT
    a.*
FROM
     attachment a
WHERE a.id = ?
SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $attachmentId);
        $stmt->execute();
        $rs = $stmt->get_result();
        $attachment = $rs->fetch_object(Attachment::class);
        $stmt->close();

        return $attachment;
    }

    public function findByParentId(string $parentType, int $parentId): array
    {
        $sql = <<<SQL
SELECT
    a.*,
    u.full_name AS submitter_name
FROM
    attachment a
    INNER JOIN user u ON (u.id = a.submitter_uid)
    WHERE a.parent_type = ? AND a.parent_id = ?
SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('si', $parentType, $parentId);
        $stmt->execute();
        $rs = $stmt->get_result();
        $attachments = $rs->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $attachments;
    }

    public function deleteById(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM attachment WHERE id = ?');
        $stmt->bind_param('i', $id);
        $result = $stmt->execute();
        $success = $result && 1 === $stmt->affected_rows;
        $stmt->close();

        return $success;
    }
}
