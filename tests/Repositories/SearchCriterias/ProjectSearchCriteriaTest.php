<?php declare(strict_types=1);

namespace Reconmap\Repositories\SearchCriterias;

use PHPUnit\Framework\TestCase;

class ProjectSearchCriteriaTest extends TestCase
{
    public function testBasics()
    {
        $subject = new ProjectSearchCriteria();
        $subject->addIsTemplateCriterion();
        $this->assertEquals(['p.is_template = ?'], $subject->getCriteria());
    }
}
