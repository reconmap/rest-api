<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Ponup\SqlBuilders\InsertQueryBuilder;
use Ponup\SqlBuilders\SelectQueryBuilder;
use Reconmap\Models\Attachment;

class AttachmentRepository extends MysqlRepository
{
    private const string TABLE_NAME = 'attachment';

    public const array UPDATABLE_COLUMNS_TYPES = [
        "submitter_uid" => "i",
        "client_file_name" => "s",
        "file_hash" => "s",
        "file_size" => "i",
        "file_mimetype" => "s"
    ];

    public function insert(Attachment $attachment): int
    {
        $insertQueryBuilder = new InsertQueryBuilder('attachment');
        $insertQueryBuilder->setColumns('parent_type, parent_id, submitter_uid, client_file_name, file_name, file_hash, file_size, file_mimetype');
        $stmt = $this->mysqlServer->prepare($insertQueryBuilder->toSql());
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

        $stmt = $this->mysqlServer->prepare($sql);
        $stmt->bind_param('i', $attachmentId);
        $stmt->execute();
        $result = $stmt->get_result();
        $attachment = $result->fetch_object(Attachment::class);
        $stmt->close();

        return $attachment;
    }

    public function findByParentId(string $parentType, int $parentId, ?string $mimeType = null): array
    {
        $queryBuilder = new SelectQueryBuilder('attachment a');
        $queryBuilder->setColumns('a.*, u.full_name AS submitter_name');
        $queryBuilder->addJoin('LEFT JOIN user u ON (u.id = a.submitter_uid)');
        $queryBuilder->setOrderBy('a.insert_ts DESC');
        $queryBuilder->setWhere('a.parent_type = ? AND a.parent_id = ?');
        if ($mimeType) {
            $queryBuilder->setWhere('AND a.file_mimetype = ?');
        }

        $stmt = $this->mysqlServer->prepare($queryBuilder->toSql());

        if ($mimeType) {
            $stmt->bind_param('sis', $parentType, $parentId, $mimeType);
        } else {
            $stmt->bind_param('si', $parentType, $parentId);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $attachments = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $attachments;
    }

    public function deleteById(int $id): bool
    {
        return $this->deleteByTableId('attachment', $id);
    }

    public function getUsage(): array
    {
        $sql = <<<SQL
        SELECT
            COUNT(*) AS total_count,
            COALESCE(SUM(file_size), 0) AS total_file_size
        FROM attachment
        SQL;

        $result = $this->mysqlServer->query($sql);
        $usage = $result->fetch_assoc();
        $result->close();

        return $usage;
    }

    public function getFileNameById(int $id): ?string
    {
        $queryBuilder = new SelectQueryBuilder('attachment');
        $queryBuilder->setColumns('file_name');
        $queryBuilder->setWhere('id = ?');
        $stmt = $this->mysqlServer->prepare($queryBuilder->toSql());
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $attachments = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        if (count($attachments) === 1) {
            return $attachments[0]['file_name'];
        }
        return null;
    }

    public function updateById(int $id, array $newColumnValues): bool
    {
        return $this->updateByTableId(self::TABLE_NAME, $id, $newColumnValues);
    }

}
