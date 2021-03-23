<?php declare(strict_types=1);

namespace Reconmap\Repositories\QueryBuilders;

class InsertQueryBuilder implements QueryBuilder
{
    private string $columns;

    public function __construct(private string $tableName)
    {
    }

    public function setColumns(string|array $columns): void
    {
        if (is_array($columns)) {
            $columns = implode(', ', $columns);
        }

        $this->columns = $columns;
    }

    public function toSql(): string
    {
        $numColumns = substr_count($this->columns, ',');
        $columnPlaceHolders = str_repeat('?, ', $numColumns) . '?';

        return sprintf("INSERT INTO %s (%s) VALUES (%s)", $this->tableName, $this->columns, $columnPlaceHolders);
    }
}
