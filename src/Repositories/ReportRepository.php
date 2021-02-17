<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\Models\Report;

class ReportRepository extends MysqlRepository
{
    public function findById(int $reportId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM report WHERE id = ?');
        $stmt->bind_param('i', $reportId);
        $stmt->execute();
        $rs = $stmt->get_result();
        $report = $rs->fetch_assoc();
        $stmt->close();

        return $report;
    }

    public function findAll(): array
    {
        $sql = <<<SQL
        SELECT
            r.*, p.id AS project_id, p.name AS project_name
            FROM report r
            INNER JOIN project p ON (p.id = r.project_id)
            ORDER BY r.insert_ts DESC
            LIMIT 20
        SQL;
        $rs = $this->db->query($sql);
        return $rs->fetch_all(MYSQLI_ASSOC);
    }

    public function insert(Report $report): int
    {
        $stmt = $this->db->prepare('INSERT INTO report (project_id, generated_by_uid, version_name, version_description) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('iiss', $report->projectId, $report->generatedByUid, $report->versionName, $report->versionDescription);
        return $this->executeInsertStatement($stmt);
    }

    public function deleteById(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM report WHERE id = ?');
        $stmt->bind_param('i', $id);
        $result = $stmt->execute();
        $success = $result && 1 === $stmt->affected_rows;
        $stmt->close();

        return $success;
    }

    public function findByProjectId(int $projectId): array
    {
        $sql = <<<SQL
SELECT
       r.*,
       (SELECT id FROM attachment WHERE parent_type = 'report' AND parent_id = r.id AND file_mimetype = 'text/html') AS html_attachment_id,
       (SELECT id FROM attachment WHERE parent_type = 'report' AND parent_id = r.id AND file_mimetype = 'application/pdf') AS pdf_attachment_id

FROM
     report r
WHERE
      r.project_id = ?
ORDER BY r.insert_ts DESC
SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $projectId);
        $stmt->execute();
        $rs = $stmt->get_result();
        $reports = $rs->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $reports;
    }
}
