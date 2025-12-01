<?php

declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\Models\Report;

class ReportRepository extends MysqlRepository
{
    public function findById(int $reportId): ?array
    {
        $stmt = $this->mysqlServer->prepare('SELECT * FROM report WHERE id = ?');
        $stmt->bind_param('i', $reportId);
        $stmt->execute();
        $result = $stmt->get_result();
        $report = $result->fetch_assoc();
        $stmt->close();

        return $report;
    }

    public function findAll(): array
    {
        $sql = <<<SQL
        SELECT
            r.*, p.id AS project_id, p.name AS project_name,
            a.id AS attachment_id, a.client_file_name
            FROM report r
            INNER JOIN project p ON (p.id = r.project_id)
            INNER JOIN attachment a ON (a.parent_id = r.id AND a.parent_type = 'report')
            ORDER BY r.created_at DESC
            LIMIT 20
        SQL;
        $result = $this->mysqlServer->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function insert(Report $report): int
    {
        $stmt = $this->mysqlServer->prepare('INSERT INTO report (project_id, created_by_uid, is_template, version_name, version_description) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('iiiss', $report->project_id, $report->created_by_uid, $report->is_template, $report->version_name, $report->version_description);
        return $this->executeInsertStatement($stmt);
    }

    public function deleteById(int $id): bool
    {
        return $this->deleteByTableId('report', $id);
    }

    public function findByProjectId(int $projectId): array
    {
        //  AND file_mimetype = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        $sql = <<<SQL
SELECT
       r.*,
       r.project_id AS projectId,
       r.created_by_uid AS createdByUid,
       r.created_at AS createdAt,
       r.version_name AS versionName,
       r.version_description AS versionDescription,
       a.id AS attachmentId,
       a.client_file_name AS clientFileName
FROM
    report r
INNER JOIN
    attachment a ON (a.parent_id = r.id AND a.parent_type = 'report')
WHERE
    r.project_id = ?
ORDER BY
    r.created_at DESC
SQL;

        $stmt = $this->mysqlServer->prepare($sql);
        $stmt->bind_param('i', $projectId);
        $stmt->execute();
        $result = $stmt->get_result();
        $reports = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $reports;
    }


    public function findTemplates(): array
    {
        $sql = <<<SQL
SELECT
       r.*,
       a.id AS attachment_id, a.created_at, a.parent_type, a.parent_id, a.created_by_uid, a.client_file_name, a.file_name, a.file_size, a.file_mimetype, a.file_hash
FROM
    report r
INNER JOIN
        attachment a ON (a.parent_id = r.id)
WHERE
    r.is_template
AND
    a.parent_type = 'report'
ORDER BY
    r.created_at DESC
SQL;

        $stmt = $this->mysqlServer->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $reports = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $reports;
    }
}
