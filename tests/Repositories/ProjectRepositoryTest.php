<?php

declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\DatabaseTestCase;

class ProjectRepositoryTest extends DatabaseTestCase
{
    private ProjectRepository $subject;

    public function setUp(): void
    {
        $db = $this->getDatabaseConnection();
        $this->subject = new ProjectRepository($db);
    }

    public function testFindTemplateProjectsReturnsNumberOfTasks()
    {
        $projects = $this->subject->findTemplateProjects(1);
        $this->assertCount(1, $projects);
        $this->assertEquals(3, $projects[0]['num_tasks']);
    }
}
