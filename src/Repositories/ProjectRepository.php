<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\Models\Project;
use Reconmap\Repositories\QueryBuilders\InsertQueryBuilder;
use Reconmap\Repositories\QueryBuilders\SelectQueryBuilder;
use Reconmap\Repositories\QueryBuilders\UpdateQueryBuilder;

class ProjectRepository extends MysqlRepository
{
    public const UPDATABLE_COLUMNS_TYPES = [
        'client_id' => 'i',
        'name' => 's',
        'description' => 's',
        'is_template' => 'i',
        'engagement_type' => 's',
        'engagement_start_date' => 's',
        'engagement_end_date' => 's',
        'archived' => 'i'
    ];

    public function findAll(): array
    {
        $queryBuilder = $this->getBaseSelectQueryBuilder();
        $queryBuilder->setLimit('20');

        $rs = $this->db->query($queryBuilder->toSql());
        return $rs->fetch_all(MYSQLI_ASSOC);
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
        $rs = $stmt->get_result();
        $project = $rs->fetch_assoc();
        $stmt->close();

        return $project;
    }

    public function findTemplateProjects(int $isTemplate): array
    {
        $sql = <<<SQL
        SELECT
            p.*,
            c.name AS client_name,
            (SELECT COUNT(*) FROM task WHERE project_id = p.id) AS num_tasks
        FROM project p
        LEFT JOIN client c ON (c.id = p.client_id)
        WHERE p.is_template = ?
        SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $isTemplate);
        $stmt->execute();
        $rs = $stmt->get_result();
        $projects = $rs->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $projects;
    }

    public function createFromTemplate(int $templateId, int $userId): array
    {
        $this->db->begin_transaction();

        $projectSql = <<<SQL
        INSERT INTO project (creator_uid, name, description, engagement_type, engagement_start_date, engagement_end_date) SELECT ?, CONCAT(name, ' - ', CURRENT_TIMESTAMP()), description, engagement_type, engagement_start_date, engagement_end_date FROM project WHERE id = ?
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
        $result = $repository->create($projectId, $userId);

        $this->db->commit();

        return [
            'projectId' => $projectId
        ];
    }

    public function insert(Project $project): int
    {
        $insertStmt = new InsertQueryBuilder('project');
        $insertStmt->setColumns('creator_uid, client_id, name, description, is_template, engagement_type, engagement_start_date, engagement_end_date');

        $stmt = $this->db->prepare($insertStmt->toSql());
        $stmt->bind_param('iississs', $project->creator_uid, $project->clientId, $project->name, $project->description, $project->is_template, $project->engagement_type, $project->engagement_start_date, $project->engagement_end_date);
        return $this->executeInsertStatement($stmt);
    }

    public function deleteById(int $id): bool
    {
        return $this->deleteByTableId('project', $id);
    }

    public function updateById(int $id, array $newColumnValues): bool
    {
        $updateQueryBuilder = new UpdateQueryBuilder('project');
        $updateQueryBuilder->setColumnValues(array_map(fn() => '?', $newColumnValues));
        $updateQueryBuilder->setWhereConditions('id = ?');

        $stmt = $this->db->prepare($updateQueryBuilder->toSql());
        call_user_func_array([$stmt, 'bind_param'], [$this->generateParamTypes(array_keys($newColumnValues)) . 'i', ...$this->refValues($newColumnValues), &$id]);
        $result = $stmt->execute();
        $success = $result && 1 === $stmt->affected_rows;
        $stmt->close();

        return $success;
    }

    private function getBaseSelectQueryBuilder(): SelectQueryBuilder
    {
        $queryBuilder = new SelectQueryBuilder('project p');
        return $queryBuilder;
    }
}
