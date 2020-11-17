<?php declare(strict_types=1);

namespace Reconmap\Repositories\QueryBuilders;

interface QueryBuilder
{
    public function toSql(): string;
}
