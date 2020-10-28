<?php

declare(strict_types=1);

namespace Reconmap\Repositories;

class ReportVersionRepository extends MysqlRepository
{
    public function findByProjectId(int $projectId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM report_version WHERE project_id = ? ORDER BY insert_ts DESC');
        $stmt->bind_param('i', $projectId);
        $stmt->execute();
        $rs = $stmt->get_result();
        $targets = $rs->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $targets;
    }
}
