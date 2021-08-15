<?php declare(strict_types=1);

namespace Reconmap\Repositories\SearchCriterias;

use PHPUnit\Framework\TestCase;

class ProjectSearchCriteriaTest extends TestCase
{
    private ProjectSearchCriteria $searchCriteria;

    protected function setUp(): void
    {
        $this->searchCriteria = new ProjectSearchCriteria();
    }

    public function testAddIsTemplateCriterion()
    {
        $this->searchCriteria->addIsTemplateCriterion();
        $this->assertEquals(['p.is_template = ?'], $this->searchCriteria->getCriteria());
        $this->assertEquals([1], $this->searchCriteria->getValues());
    }

    public function testAddIsNotTemplateCriterion()
    {
        $this->searchCriteria->addIsNotTemplateCriterion();
        $this->assertEquals(['p.is_template = ?'], $this->searchCriteria->getCriteria());
        $this->assertEquals([0], $this->searchCriteria->getValues());
    }
}
