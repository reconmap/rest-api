<?php declare(strict_types=1);

namespace Reconmap\Repositories\QueryBuilders;

class DeleteQueryBuilder implements QueryBuilder
{
    public function __construct(private string $tableName)
    {
        if (empty($this->tableName))
            throw new \InvalidArgumentException('the constructor argument table name can not be empty or null');
    }

    public function toSql(): string
    {
        return sprintf("DELETE FROM %s WHERE id = ?", $this->tableName);
    }
}
