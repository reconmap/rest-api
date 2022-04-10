<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Ponup\SqlBuilders\InsertQueryBuilder;
use Ponup\SqlBuilders\SearchCriteria;
use Ponup\SqlBuilders\SelectQueryBuilder;
use Reconmap\Models\Task;
use Reconmap\Repositories\SearchCriterias\TaskSearchCriteria;
use Reconmap\Services\PaginationRequestHandler;

class TaskRepository extends MysqlRepository implements Findable
{
    public const UPDATABLE_COLUMNS_TYPES = [
        'project_id' => 'i',
        'assignee_uid' => 'i',
        'priority' => 's',
        'summary' => 's',
        'description' => 's',
        'command_id' => 'i',
        'status' => 's',
        'due_date' => 's'
    ];

    public function findAll(bool $excludeTemplateTasks = true, ?int $limit = 20): array
    {
        $selectQueryBuilder = $this->getBaseSelectQueryBuilder();
        if ($excludeTemplateTasks) {
            $selectQueryBuilder->setWhere('p.is_template IS FALSE');
        }
        if (!is_null($limit)) {
            $selectQueryBuilder->setLimit($limit);
        }
        $sql = $selectQueryBuilder->toSql();

        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function findByKeywords(string $keywords): array
    {
        $queryBuilder = $this->getBaseSelectQueryBuilder();
        $queryBuilder->setLimit('20');
        $queryBuilder->setWhere('t.summary LIKE ? OR t.description LIKE ?');
        $sql = $queryBuilder->toSql();

        $keywordsLike = "%$keywords%";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ss', $keywordsLike, $keywordsLike);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function findByProjectId(int $projectId): array
    {
        $searchCriteria = new TaskSearchCriteria();
        $searchCriteria->addProjectCriterion($projectId);
        return $this->search($searchCriteria);
    }

    public function search(SearchCriteria $searchCriteria, ?PaginationRequestHandler $paginator = null): array
    {
        $queryBuilder = $this->getBaseSelectQueryBuilder();
        return $this->searchAll($queryBuilder, $searchCriteria, $paginator);
    }

    protected function getBaseSelectQueryBuilder(): SelectQueryBuilder
    {
        $queryBuilder = new SelectQueryBuilder('task t');
        $queryBuilder->setColumns('
            t.id, t.project_id, p.name AS project_name, t.insert_ts, t.update_ts, t.priority, t.summary, t.description, t.status, t.due_date,
            t.creator_uid, creator.full_name AS creator_full_name,
            t.assignee_uid, assignee.full_name AS assignee_full_name,
            c.id AS command_id, c.name AS command_name, c.output_parser, c.docker_image AS command_docker_image, c.arguments AS command_container_args
        ');
        $queryBuilder->addJoin('INNER JOIN user creator ON (creator.id = t.creator_uid)');
        $queryBuilder->addJoin('LEFT JOIN user assignee ON (assignee.id = t.assignee_uid)');
        $queryBuilder->addJoin('LEFT JOIN project p ON (p.id = t.project_id)');
        $queryBuilder->addJoin('LEFT JOIN command c ON (c.id = t.command_id)');
        $queryBuilder->setOrderBy('t.insert_ts DESC');
        return $queryBuilder;
    }

    public function findById(int $id): ?array
    {
        $queryBuilder = $this->getBaseSelectQueryBuilder();
        $queryBuilder->setWhere('t.id = ?');
        $sql = $queryBuilder->toSql();

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $task = $result->fetch_assoc();
        $stmt->close();

        return $task;
    }

    public function deleteById(int $id): bool
    {
        return $this->deleteByTableId('task', $id);
    }

    public function deleteByIds(array $ids): int
    {
        return $this->deleteByTableIds('task', $ids);
    }

    public function updateById(int $id, array $newColumnValues): bool
    {
        return $this->updateByTableId('task', $id, $newColumnValues);
    }

    public function insert(Task $task): int
    {
        $insertStmt = new InsertQueryBuilder('task');
        $insertStmt->setColumns('creator_uid, project_id, priority, summary, description, due_date, command_id');
        $stmt = $this->db->prepare($insertStmt->toSql());
        $stmt->bind_param('iissssi', $task->creator_uid, $task->project_id, $task->priority, $task->summary, $task->description, $task->due_date, $task->command_id);
        return $this->executeInsertStatement($stmt);
    }
}
