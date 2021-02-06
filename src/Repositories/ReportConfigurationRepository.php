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

    public function findByProjectId(int $projectId): ReportConfiguration
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

        if (is_null($configuration)) {
            $configuration = new ReportConfiguration();
        }

        return $configuration;
    }

    public function insert(object $reportConfiguration): int
    {
        /**
         * @var ReportConfiguration $reportConfiguration
         */
        $stmt = $this->db->prepare('REPLACE INTO report_configuration
    (project_id, include_toc, include_revisions_table, include_team_bios, include_findings_overview, include_cover, include_header, include_footer,  custom_cover, custom_header, custom_footer) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('iiiiissssss', $reportConfiguration->project_id, $reportConfiguration->include_toc, $reportConfiguration->include_revisions_table, $reportConfiguration->include_team_bios, $reportConfiguration->include_findings_overview,
            $reportConfiguration->include_cover, $reportConfiguration->include_header, $reportConfiguration->include_footer, $reportConfiguration->custom_cover, $reportConfiguration->custom_header, $reportConfiguration->custom_footer);
        return $this->executeInsertStatement($stmt);
    }
}
