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

        $stmt = $this->mysqlServer->prepare($sql);
        $stmt->bind_param('i', $projectId);
        $stmt->execute();
        $result = $stmt->get_result();
        $configuration = $result->fetch_object(ReportConfiguration::class);
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
        $stmt = $this->mysqlServer->prepare('REPLACE INTO report_configuration (project_id) VALUES (?)');
        $stmt->bind_param('i', $reportConfiguration->project_id);
        return $this->executeInsertStatement($stmt);
    }
}
