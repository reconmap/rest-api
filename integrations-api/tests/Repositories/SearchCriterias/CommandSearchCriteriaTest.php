<?php declare(strict_types=1);

namespace Reconmap\Repositories\SearchCriterias;

use PHPUnit\Framework\TestCase;

class CommandSearchCriteriaTest extends TestCase
{
    private CommandSearchCriteria $searchCriteria;

    protected function setUp(): void
    {
        $this->searchCriteria = new CommandSearchCriteria();
    }

    public function testAddIsTemplateCriterion()
    {
        $this->searchCriteria->addKeywordsCriterion('foo bar');
        $this->assertEquals(['(c.name LIKE ? OR c.description LIKE ? OR JSON_CONTAINS(c.tags, ?))'], $this->searchCriteria->getCriteria());
        $this->assertEquals(['%foo bar%', '%foo bar%', '"foo bar"'], $this->searchCriteria->getValues());
    }
}
