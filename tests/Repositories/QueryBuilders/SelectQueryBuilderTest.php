<?php declare(strict_types=1);

namespace Reconmap\Repositories\QueryBuilders;

use PHPUnit\Framework\TestCase;

class SelectQueryBuilderTest extends TestCase
{
    public function testBasicSelect(): void
    {
        $queryBuilder = new SelectQueryBuilder('foo');
        $this->assertEqualsCanonicalizing('SELECT * FROM foo', $queryBuilder->toSql());
    }

    public function testBasicSelectWithColumns(): void
    {
        $queryBuilder = new SelectQueryBuilder('foo');
        $queryBuilder->setColumns('bar, baz');
        $this->assertEqualsCanonicalizing('SELECT bar, baz FROM foo', $queryBuilder->toSql());
    }

    public function testBasicSelectWithLimit(): void
    {
        $queryBuilder = new SelectQueryBuilder('foo');
        $queryBuilder->setColumns('bar, baz');
        $queryBuilder->setLimit('0, 20');
        $this->assertEqualsCanonicalizing('SELECT bar, baz FROM foo LIMIT 0, 20', $queryBuilder->toSql());
    }

    public function testBasicSelectWithLimitAndOrderBy(): void
    {
        $queryBuilder = new SelectQueryBuilder('foo');
        $queryBuilder->setColumns('bar, baz');
        $queryBuilder->setLimit('0, 20');
        $queryBuilder->setOrderBy('bar DESC');
        $this->assertEqualsCanonicalizing('SELECT bar, baz FROM foo ORDER BY bar DESC LIMIT 0, 20', $queryBuilder->toSql());
    }
}
