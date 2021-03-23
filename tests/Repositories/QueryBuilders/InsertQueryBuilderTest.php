<?php declare(strict_types=1);

namespace Reconmap\Repositories\QueryBuilders;

use PHPUnit\Framework\TestCase;

class InsertQueryBuilderTest extends TestCase
{
    public function testStringColumns()
    {
        $queryBuilder = new InsertQueryBuilder('table');
        $queryBuilder->setColumns('foo, bar, baz');

        $this->assertEquals('INSERT INTO table (foo, bar, baz) VALUES (?, ?, ?)', $queryBuilder->toSql());
    }

    public function testArrayColumns()
    {
        $queryBuilder = new InsertQueryBuilder('table');
        $queryBuilder->setColumns(['this', 'is', 'a_test']);

        $this->assertEquals('INSERT INTO table (this, is, a_test) VALUES (?, ?, ?)', $queryBuilder->toSql());
    }
}
