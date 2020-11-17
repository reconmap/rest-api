<?php declare(strict_types=1);

namespace Reconmap\Repositories\QueryBuilders;

use PHPUnit\Framework\TestCase;

class UpdateQueryBuilderTest extends TestCase
{

    public function testSqlGeneration()
    {
        $queryBuilder = new UpdateQueryBuilder('person');
        $queryBuilder->setColumnValues([
            'email' => 'NULL',
            'age' => 42,
            'weight' => 100,
            'code' => '?'
        ]);
        $queryBuilder->setWhereConditions('id = ?');
        $this->assertEquals('UPDATE person SET email = NULL, age = 42, weight = 100, code = ? WHERE id = ?', $queryBuilder->toSql());
    }
}
