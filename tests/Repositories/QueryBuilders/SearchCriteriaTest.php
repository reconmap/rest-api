<?php declare(strict_types=1);

namespace Reconmap\Repositories\QueryBuilders;

use PHPUnit\Framework\TestCase;

class SearchCriteriaTest extends TestCase
{
    public function testSetterGetters()
    {
        $criteria = new SearchCriteria();
        $criteria->addCriterion('v.foo = ?', [1]);
        $criteria->addCriterion('v.bar = ?', ['yes']);

        $this->assertEquals(['v.foo = ?', 'v.bar = ?'], $criteria->getCriteria());
        $this->assertEquals([1, 'yes'], $criteria->getValues());
    }
}
