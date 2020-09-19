<?php

declare(strict_types=1);

namespace Reconmap\Repositories;

use PHPUnit\Framework\TestCase;

class ProjectRepositoryTest extends TestCase
{
    private ProjectRepository $subject;

    public function setUp(): void
    {
        $db = new \mysqli('db', 'reconmapper', 'reconmapped', 'reconmap');
        $this->subject = new ProjectRepository($db);
    }

    public function testFindTemplateProjectsReturnsNumberOfTasks()
    {
        $projects = $this->subject->findTemplateProjects(1);
        $this->assertCount(1, $projects);
        $this->assertEquals(3, $projects[0]['num_tasks']);
    }
}
