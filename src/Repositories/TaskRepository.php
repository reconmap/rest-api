<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\Models\Task;
use Reconmap\Repositories\QueryBuilders\SelectQueryBuilder;
use Reconmap\Repositories\QueryBuilders\UpdateQueryBuilder;

class TaskRepository extends MysqlRepository
{
    public const UPDATABLE_COLUMNS_TYPES = [
        'project_id' => 'i',
        'summary' => 's',
        'description' => 's',
        'command_id' => 'i',
        'assignee_uid' => 'i',
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

        $rs = $this->db->query($sql);
        return $rs->fetch_all(MYSQLI_ASSOC);
    }

    public function findByKeywords(string $keywords): array
    {
        $queryBuilder = $this->getBaseSelectQueryBuilder();
        $queryBuilder->setLimit('20');
        $queryBuilder->setWhere('t.name LIKE ? OR t.description LIKE ?');
        $sql = $queryBuilder->toSql();

        $keywordsLike = "%$keywords%";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ss', $keywordsLike, $keywordsLike);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    private function getBaseSelectQueryBuilder(): SelectQueryBuilder
    {
        $queryBuilder = new SelectQueryBuilder('task t');
        $queryBuilder->setColumns('
            t.id, t.project_id, p.name AS project_name, t.insert_ts, t.update_ts, t.summary, t.description, t.status, t.due_date,
            t.creator_uid, creator.full_name AS creator_full_name,
            t.assignee_uid, assignee.full_name AS assignee_full_name,
            t.command_id, c.short_name AS command_short_name, c.docker_image AS command_docker_image, c.arguments AS command_container_args
        ');
        $queryBuilder->addJoin('INNER JOIN user creator ON (creator.id = t.creator_uid)');
        $queryBuilder->addJoin('LEFT JOIN user assignee ON (assignee.id = t.assignee_uid)');
        $queryBuilder->addJoin('LEFT JOIN project p ON (p.id = t.project_id)');
        $queryBuilder->addJoin('LEFT JOIN command c ON (c.id = t.command_id)');
        $queryBuilder->setOrderBy('t.insert_ts DESC');
        return $queryBuilder;
    }

    public function findById(int $id): array
    {
        $queryBuilder = $this->getBaseSelectQueryBuilder();
        $queryBuilder->setWhere('t.id = ?');
        $sql = $queryBuilder->toSql();

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $rs = $stmt->get_result();
        $project = $rs->fetch_assoc();
        $stmt->close();

        return $project;
    }

    public function findByProjectId(int $projectId): array
    {
        $selectQueryBuilder = $this->getBaseSelectQueryBuilder();
        $selectQueryBuilder->setWhere('project_id = ?');
        $selectQueryBuilder->setLimit('20');
        $sql = $selectQueryBuilder->toSql();

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $projectId);
        $stmt->execute();
        $rs = $stmt->get_result();
        $tasks = $rs->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $tasks;
    }

    public function deleteById(int $id): bool
    {
        return $this->deleteByTableId('task', $id);
    }

    public function updateById(int $id, array $newColumnValues): bool
    {
        $updateQueryBuilder = new UpdateQueryBuilder('task');
        $updateQueryBuilder->setColumnValues(array_map(fn() => '?', $newColumnValues));
        $updateQueryBuilder->setWhereConditions('id = ?');

        $stmt = $this->db->prepare($updateQueryBuilder->toSql());
        call_user_func_array([$stmt, 'bind_param'], [$this->generateParamTypes(array_keys($newColumnValues)) . 'i', ...$this->refValues($newColumnValues), &$id]);
        $result = $stmt->execute();
        $success = $result && 1 === $stmt->affected_rows;
        $stmt->close();

        return $success;
    }

    public function insert(object $task): int
    {
        /** @var Task $task */
        $stmt = $this->db->prepare('INSERT INTO task (creator_uid, project_id, summary, description, due_date, command_id) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('iisssi', $task->creator_uid, $task->project_id, $task->summary, $task->description, $task->due_date, $task->command_id);
        return $this->executeInsertStatement($stmt);
    }
}
