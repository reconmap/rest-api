<?php declare(strict_types=1);

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
            ORDER BY r.insert_ts DESC
            LIMIT 20
        SQL;
        $result = $this->mysqlServer->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function insert(Report $report): int
    {
        $stmt = $this->mysqlServer->prepare('INSERT INTO report (project_id, generated_by_uid, is_template, version_name, version_description) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('iiiss', $report->projectId, $report->generatedByUid, $report->is_template, $report->versionName, $report->versionDescription);
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
       a.id AS attachment_id,
       a.client_file_name
FROM
    report r
INNER JOIN
    attachment a ON (a.parent_id = r.id AND a.parent_type = 'report')
WHERE
    r.project_id = ?
ORDER BY
    r.insert_ts DESC
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
       a.id AS attachment_id, a.insert_ts, a.parent_type, a.parent_id, a.submitter_uid, a.client_file_name, a.file_name, a.file_size, a.file_mimetype, a.file_hash
FROM
    report r
INNER JOIN
        attachment a ON (a.parent_id = r.id)
WHERE
    r.is_template
AND
    a.parent_type = 'report'
ORDER BY
    r.insert_ts DESC
SQL;

        $stmt = $this->mysqlServer->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $reports = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $reports;
    }
}
