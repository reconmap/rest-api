<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Ponup\SqlBuilders\SearchCriteria;
use Ponup\SqlBuilders\SelectQueryBuilder;
use Reconmap\Models\Target;
use Reconmap\Services\PaginationRequestHandler;

class TargetRepository extends MysqlRepository implements Findable
{
    public const array UPDATABLE_COLUMNS_TYPES = [
        'project_id' => 'i',
        'name' => 's',
        'kind' => 's',
        'tags' => 's',
    ];

    public function findById(int $id): ?array
    {
        $queryBuilder = $this->getBaseSelectQueryBuilder();
        $queryBuilder->setWhere('t.id = ?');
        $stmt = $this->mysqlServer->prepare($queryBuilder->toSql());
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $target = $result->fetch_assoc();
        $stmt->close();

        return $target;
    }

    public function findAll(): array
    {
        $queryBuilder = $this->getBaseSelectQueryBuilder();
        $result = $this->mysqlServer->query($queryBuilder->toSql());
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function findOrInsert(Target $target): int
    {
        $queryBuilder = $this->getBaseSelectQueryBuilder();
        if ($target->parent_id !== null) {
            $queryBuilder->setWhere('t.project_id = ? AND t.parent_id = ? AND t.name = ?');
        } else {
            $queryBuilder->setWhere('t.project_id = ? AND t.parent_id IS NULL AND t.name = ?');
        }

        $stmt = $this->mysqlServer->prepare($queryBuilder->toSql());
        if ($target->parent_id !== null) {
            $stmt->bind_param('iis', $target->project_id, $target->parent_id, $target->name);
        } else {
            $stmt->bind_param('is', $target->project_id, $target->name);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $dbTarget = $result->fetch_object();
        $stmt->close();

        if ($dbTarget) {
            return $dbTarget->id;
        }

        return $this->insert($target);
    }

    public function search(SearchCriteria $searchCriteria, ?PaginationRequestHandler $paginator = null): array
    {
        $queryBuilder = $this->getBaseSelectQueryBuilder();
        $queryBuilder->setOrderBy('PARENT_CHILD_NAME(parent_name, t.name)');
        return $this->searchAll($queryBuilder, $searchCriteria, $paginator);
    }

    public function countSearch(SearchCriteria $searchCriteria): int
    {
        $queryBuilder = $this->getBaseSelectQueryBuilder();
        return $this->countSearchResults($queryBuilder, $searchCriteria);
    }

    public function deleteById(int $id): bool
    {
        return $this->deleteByTableId('target', $id);
    }

    public function insert(Target $target): int
    {
        $stmt = $this->mysqlServer->prepare('INSERT INTO target (project_id, parent_id, name, kind, tags) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('iisss', $target->project_id, $target->parent_id, $target->name, $target->kind, $target->tags);
        return $this->executeInsertStatement($stmt);
    }

    protected function getBaseSelectQueryBuilder(): SelectQueryBuilder
    {
        $queryBuilder = new SelectQueryBuilder('target t');
        $queryBuilder->addJoin('LEFT JOIN target parent_t ON (parent_t.id = t.parent_id)');
        $queryBuilder->setColumns('
            t.*,
            t.id, t.name,
            t.parent_id, parent_t.name AS parent_name,
            (SELECT COUNT(*) FROM vulnerability WHERE target_id = t.id) AS num_vulnerabilities
        ');
        $queryBuilder->setOrderBy('t.insert_ts DESC, t.name ASC');
        return $queryBuilder;
    }
}
