<?php declare(strict_types=1);

namespace Reconmap\Repositories\QueryBuilders;

class UpdateQueryBuilder implements QueryBuilder
{
    private string $table;
    private array $columnValues;
    private ?string $whereConditions;

    public function __construct(string $table)
    {
        $this->table = $table;
        $this->columnValues = [];
        $this->whereConditions = null;
    }

    public function setWhereConditions(string $whereConditions): void
    {
        $this->whereConditions = $whereConditions;
    }

    public function setColumnValues(array $columnValues): void
    {
        $this->columnValues = $columnValues;
    }

    public function toSql(): string
    {
        $columnAndValues = implode(', ', array_map(function ($name, $value) {
            return sprintf("%s = %s", $name, $value);
        }, array_keys($this->columnValues), $this->columnValues));
        return sprintf('UPDATE %s SET %s WHERE %s', $this->table, $columnAndValues, $this->whereConditions);
    }
}
