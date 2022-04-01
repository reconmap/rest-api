<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Ponup\SqlBuilders\InsertQueryBuilder;
use Ponup\SqlBuilders\SearchCriteria;
use Ponup\SqlBuilders\SelectQueryBuilder;
use Reconmap\Models\Project;
use Reconmap\Services\PaginationRequestHandler;

class ProjectRepository extends MysqlRepository
{
    public const UPDATABLE_COLUMNS_TYPES = [
        'client_id' => 'i',
        'name' => 's',
        'description' => 's',
        'visibility' => 's',
        'is_template' => 'i',
        'engagement_type' => 's',
        'engagement_start_date' => 's',
        'engagement_end_date' => 's',
        'archived' => 'i',
        'external_id' => 's',
        'vulnerability_metrics' => 's',
        'management_summary' => 's',
        'management_conclusion' => 's'
    ];

    public function isVisibleToUser(int $projectId, int $userId): bool
    {
        $sql = <<<SQL
SELECT 1
FROM project p
LEFT JOIN project_user pu ON (pu.project_id = p.id)
WHERE p.id = ? AND (p.visibility = 'public' OR pu.user_id = ?)
SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ii', $projectId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();

        return $exists;
    }

    public function findById(int $id): ?array
    {
        $sql = <<<SQL
SELECT
       p.*,
       c.name AS client_name,
       u.full_name AS creator_full_name
FROM
    project p
    INNER JOIN user u ON (u.id = p.creator_uid)
    LEFT JOIN client c ON (c.id = p.client_id)
WHERE
      p.id = ?
SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $project = $result->fetch_assoc();
        $stmt->close();

        return $project;
    }

    public function search(SearchCriteria $searchCriteria, ?PaginationRequestHandler $paginator = null): array
    {
        $queryBuilder = $this->getBaseSelectQueryBuilder();
        return $this->searchAll($queryBuilder, $searchCriteria, $paginator);
    }

    public function clone(int $templateId, int $userId): array
    {
        $this->db->begin_transaction();

        $projectSql = <<<SQL
        INSERT INTO project (creator_uid, name, description, engagement_type, engagement_start_date, engagement_end_date, vulnerability_metrics, management_summary, management_conclusion) SELECT ?, CONCAT(name, ' - ', CURRENT_TIMESTAMP()), description, engagement_type, engagement_start_date, engagement_end_date, vulnerability_metrics, management_summary, management_conclusion FROM project WHERE id = ?
        SQL;
        $stmt = $this->db->prepare($projectSql);
        $stmt->bind_param('ii', $userId, $templateId);
        $projectId = $this->executeInsertStatement($stmt);

        $tasksSql = <<<SQL
        INSERT INTO task (project_id, creator_uid, command_id, summary, description) SELECT ?, creator_uid, command_id, summary, description FROM task WHERE project_id = ?
        SQL;
        $stmt = $this->db->prepare($tasksSql);
        $stmt->bind_param('ii', $projectId, $templateId);
        $this->executeInsertStatement($stmt);

        $repository = new ProjectUserRepository($this->db);
        $repository->create($projectId, $userId);

        $this->db->commit();

        return ['projectId' => $projectId];
    }

    public function insert(Project $project): int
    {
        $insertStmt = new InsertQueryBuilder('project');
        $insertStmt->setColumns('creator_uid, client_id, name, description, is_template, engagement_type, engagement_start_date, engagement_end_date, visibility, external_id, vulnerability_metrics, management_summary, management_conclusion');

        $stmt = $this->db->prepare($insertStmt->toSql());
        $stmt->bind_param('iississssssss', $project->creator_uid, $project->client_id, $project->name, $project->description, $project->is_template, $project->engagement_type, $project->engagement_start_date, $project->engagement_end_date, $project->visibility, $project->external_id, $project->vulnerability_metrics, $project->management_summary, $project->management_conclusion);
        return $this->executeInsertStatement($stmt);
    }

    public function deleteById(int $id): bool
    {
        return $this->deleteByTableId('project', $id);
    }

    public function updateById(int $id, array $newColumnValues): bool
    {
        return $this->updateByTableId('project', $id, $newColumnValues);
    }

    protected function getBaseSelectQueryBuilder(): SelectQueryBuilder
    {
        $queryBuilder = new SelectQueryBuilder('project p');
        $queryBuilder->setColumns('
            p.*,
            c.name AS client_name,
            (SELECT COUNT(*) FROM task WHERE project_id = p.id) AS num_tasks
        ');
        $queryBuilder->addJoin('LEFT JOIN client c ON (c.id = p.client_id)');
        return $queryBuilder;
    }
}
