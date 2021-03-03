<?php declare(strict_types=1);

namespace Reconmap\Repositories\QueryBuilders;

use PHPUnit\Framework\TestCase;

class DeleteQueryBuilderTest extends TestCase
{
    public function testSqlGeneration()
    {
        $subject = new DeleteQueryBuilder('foo');
        $this->assertEquals('DELETE FROM foo WHERE id = ?', $subject->toSql());
    }

    public function testConstructorArgumentValidation()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('the constructor argument table name can not be empty or null');

        new DeleteQueryBuilder('');
    }
}
