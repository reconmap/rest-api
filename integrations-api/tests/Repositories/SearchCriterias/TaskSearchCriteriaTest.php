<?php declare(strict_types=1);

namespace Reconmap\Repositories\SearchCriterias;

use PHPUnit\Framework\TestCase;

class TaskSearchCriteriaTest extends TestCase
{
    public function testTemplate()
    {
        $subject = new TaskSearchCriteria();
        $subject->addProjectIsNotTemplateCriterion();
        $this->assertEquals(['p.is_template = ?'], $subject->getCriteria());
    }
}
