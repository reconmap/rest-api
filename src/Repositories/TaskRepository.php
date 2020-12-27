<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\Models\Task;
use Reconmap\Repositories\QueryBuilders\SelectQueryBuilder;

class TaskRepository extends MysqlRepository
{
    public function findAll(): array
    {
        $selectQueryBuilder = $this->getBaseSelectQueryBuilder();
        $sql = $selectQueryBuilder->toSql();

        $rs = $this->db->query($sql);
        return $rs->fetch_all(MYSQLI_ASSOC);
    }

    public function findByKeywords(string $keywords): array
    {
        $queryBuilder = $this->getBaseSelectQueryBuilder();
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
        $queryBuilder->setColumns('t.id, t.project_id, p.name AS project_name, t.insert_ts, t.update_ts, t.command, t.command_parser, t.name, t.description, t.completed, t.assignee_uid, u.name AS assignee_name');
        $queryBuilder->addJoin('LEFT JOIN user u ON (u.id = t.assignee_uid)');
        $queryBuilder->addJoin('LEFT JOIN project p ON (p.id = t.project_id)');
        $queryBuilder->setWhere('p.is_template IS FALSE');
        $queryBuilder->setOrderBy('t.insert_ts DESC');
        $queryBuilder->setLimit('20');
        return $queryBuilder;
    }

    public function findById(int $id): array
    {
        $stmt = $this->db->prepare('SELECT * FROM task WHERE id = ?');
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
        $stmt = $this->db->prepare('DELETE FROM task WHERE id = ?');
        $stmt->bind_param('i', $id);
        $result = $stmt->execute();
        $success = $result && 1 === $stmt->affected_rows;
        $stmt->close();

        return $success;
    }

    public function updateById(int $id, string $column, ?string $value): bool
    {
        $stmt = $this->db->prepare('UPDATE task SET ' . $column . ' = ? WHERE id = ?');
        $stmt->bind_param('si', $value, $id);
        $result = $stmt->execute();
        $success = $result && 1 === $stmt->affected_rows;
        $stmt->close();

        return $success;
    }

    public function insert(object $task): int
    {
        /** @var Task $task */
        $stmt = $this->db->prepare('INSERT INTO task (project_id, name, description, command, command_parser) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('issss', $task->project_id, $task->name, $task->description, $task->command, $task->command_parser);
        return $this->executeInsertStatement($stmt);
    }
}
