<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Ponup\SqlBuilders\InsertQueryBuilder;
use Ponup\SqlBuilders\SearchCriteria;
use Ponup\SqlBuilders\SelectQueryBuilder;
use Reconmap\Models\Project;
use Reconmap\Services\PaginationRequestHandler;

class ProjectRepository extends MysqlRepository
{
    public const array UPDATABLE_COLUMNS_TYPES = [
        'service_provider_id' => 'i',
        'client_id' => 'i',
        'category_id' => 'i',
        'name' => 's',
        'description' => 's',
        'visibility' => 's',
        'is_template' => 'i',
        'engagement_start_date' => 's',
        'engagement_end_date' => 's',
        'archived' => 'i',
        'external_id' => 's',
        'vulnerability_metrics' => 's',
    ];

    public function isVisibleToUser(int $projectId, int $userId): bool
    {
        $sql = <<<SQL
SELECT 1
FROM project p
LEFT JOIN project_user pu ON (pu.project_id = p.id)
WHERE p.id = ? AND (p.visibility = 'public' OR pu.user_id = ?)
SQL;

        $stmt = $this->mysqlServer->prepare($sql);
        $stmt->bind_param('ii', $projectId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();

        return $exists;
    }

    public function findById(int $id): ?array
    {
        $queryBuilder = $this->getBaseSelectQueryBuilder();
        $queryBuilder->setWhere('p.id = ?');

        $stmt = $this->mysqlServer->prepare($queryBuilder->toSql());
        $stmt->execute([$id]);
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
        $this->mysqlServer->begin_transaction();

        $projectSql = <<<SQL
        INSERT INTO project (creator_uid, name, description, category_id, engagement_start_date, engagement_end_date, vulnerability_metrics) SELECT ?, CONCAT(name, ' - ', CURRENT_TIMESTAMP()), description, category_id, engagement_start_date, engagement_end_date, vulnerability_metrics FROM project WHERE id = ?
        SQL;
        $stmt = $this->mysqlServer->prepare($projectSql);
        $stmt->bind_param('ii', $userId, $templateId);
        $projectId = $this->executeInsertStatement($stmt);

        $tasksSql = <<<SQL
        INSERT INTO task (project_id, creator_uid, command_id, summary, description, priority) SELECT ?, creator_uid, command_id, summary, description, priority FROM task WHERE project_id = ?
        SQL;
        $stmt = $this->mysqlServer->prepare($tasksSql);
        $stmt->bind_param('ii', $projectId, $templateId);
        $this->executeInsertStatement($stmt);

        $repository = new ProjectUserRepository($this->mysqlServer);
        $repository->create($projectId, $userId);

        $this->mysqlServer->commit();

        return ['projectId' => $projectId];
    }

    public function insert(Project $project): int
    {
        $insertStmt = new InsertQueryBuilder('project');
        $insertStmt->setColumns('creator_uid, service_provider_id, client_id, name, description, is_template, category_id, engagement_start_date, engagement_end_date, visibility, external_id, vulnerability_metrics');

        $stmt = $this->mysqlServer->prepare($insertStmt->toSql());
        $stmt->bind_param('iiississssss', $project->creator_uid, $project->service_provider_id, $project->client_id, $project->name, $project->description, $project->is_template, $project->category_id, $project->engagement_start_date, $project->engagement_end_date, $project->visibility, $project->external_id, $project->vulnerability_metrics);
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
            pc.name AS category_name,
            u.full_name AS creator_full_name,
            (SELECT COUNT(*) FROM task WHERE project_id = p.id) AS num_tasks
        ');
        $queryBuilder->addJoin('LEFT JOIN client c ON (c.id = p.client_id)');
        $queryBuilder->addJoin('LEFT JOIN project_category pc ON (pc.id = p.category_id)');
        $queryBuilder->addJoin('INNER JOIN user u ON (u.id = p.creator_uid)');
        return $queryBuilder;
    }
}
