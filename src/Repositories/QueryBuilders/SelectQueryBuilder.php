<?php declare(strict_types=1);

namespace Reconmap\Repositories\QueryBuilders;

class SelectQueryBuilder implements QueryBuilder
{
    private string $columns;
    private string $from;
    private array $joins;
    private array $where;
    private ?string $orderBy = null;
    private string|int|null $limit = null;

    public function __construct(string $from)
    {
        $this->columns = '*';
        $this->joins = [];
        $this->from = $from;
        $this->where = [];
    }

    public function setColumns(string $columns): void
    {
        $this->columns = $columns;
    }

    public function setWhere(string $where): void
    {
        $this->where[] = $where;
    }

    public function addJoin(string $join): void
    {
        $this->joins[] = $join;
    }

    public function setOrderBy(string $orderBy): void
    {
        $this->orderBy = $orderBy;
    }

    public function setLimit(string|int $limit): void
    {
        $this->limit = $limit;
    }

    public function toSql(): string
    {
        $sql = 'SELECT ' . $this->columns . ' FROM ' . $this->from;
        if (!empty($this->joins)) {
            $sql .= ' ' . implode(' ', $this->joins);
        }
        if (count($this->where)) {
            $sql .= ' WHERE ' . implode(' ', $this->where);
        }
        if ($this->orderBy) {
            $sql .= ' ORDER BY ' . $this->orderBy;
        }
        if ($this->limit) {
            $sql .= ' LIMIT ' . $this->limit;
        }

        return $sql;
    }
}
