<?php declare(strict_types=1);

namespace Reconmap\Database;

class NullColumnReplacer
{
    public static function replaceEmptyWithNulls(array $columns, array &$columnValues): void
    {
        foreach ($columns as $column) {
            if (array_key_exists($column, $columnValues) && $columnValues[$column] === '') {
                $columnValues[$column] = null;
            }
        }
    }
}
