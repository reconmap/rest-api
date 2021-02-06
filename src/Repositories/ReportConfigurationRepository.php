<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\Models\ReportConfiguration;

class ReportConfigurationRepository extends MysqlRepository
{
    public const UPDATABLE_COLUMNS_TYPES = [
        'optional_sections' => 's',
        'custom_cover' => 's',
        'custom_header' => 's',
        'custom_footer' => 's'
    ];

    public function findByProjectId(int $projectId): ?ReportConfiguration
    {
        $sql = <<<SQL
SELECT * FROM report_configuration WHERE project_id = ?
SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $projectId);
        $stmt->execute();
        $rs = $stmt->get_result();
        $configuration = $rs->fetch_object(ReportConfiguration::class);
        $stmt->close();

        return $configuration;
    }

    public function insert(object $reportConfiguration): int
    {
        $stmt = $this->db->prepare('REPLACE INTO report_configuration (project_id, optional_sections, custom_cover, custom_header, custom_footer) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('issss', $reportConfiguration->project_id, $reportConfiguration->optional_sections, $reportConfiguration->custom_cover, $reportConfiguration->custom_header, $reportConfiguration->custom_footer);
        return $this->executeInsertStatement($stmt);
    }
}
