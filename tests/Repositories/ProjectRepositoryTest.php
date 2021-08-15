<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\DatabaseTestCase;
use Reconmap\Models\Project;
use Reconmap\Repositories\QueryBuilders\SearchCriteria;

class ProjectRepositoryTest extends DatabaseTestCase
{
    private ProjectRepository $subject;

    public function setUp(): void
    {
        $db = $this->getDatabaseConnection();
        $this->subject = new ProjectRepository($db);
    }

    public function testFindById()
    {
        $expectedProject = [
            'id' => 1,
            'update_ts' => null,
            'creator_uid' => 1,
            'client_id' => null,
            'is_template' => 1,
            'visibility' => 'public',
            'name' => 'Linux host template',
            'description' => 'Project template to show general linux host reconnaissance tasks',
            'engagement_type' => null,
            'engagement_start_date' => null,
            'engagement_end_date' => null,
            'archived' => 0,
            'archive_ts' => null,
            'client_name' => null,
            'creator_full_name' => 'Jane Doe'
        ];

        $project1 = $this->subject->findById(1);
        unset($project1['insert_ts']); // Can't compare against a changing date/time
        $this->assertEquals($expectedProject, $project1);
    }

    public function testClone()
    {
        $result = $this->subject->clone(1, 1);
        $this->assertIsArray($result);
    }

    public function testFindTemplateProjectsReturnsNumberOfTasks()
    {
        $searchCriteria = new SearchCriteria();
        $searchCriteria->addCriterion('p.is_template = 1');
        $projects = $this->subject->search($searchCriteria);
        $this->assertCount(1, $projects);
        $this->assertEquals(3, $projects[0]['num_tasks']);
    }

    public function testIsVisibleToUser()
    {
        $this->assertTrue($this->subject->isVisibleToUser(2, 1));
    }

    public function testIsNotVisibleToUser()
    {
        $this->assertFalse($this->subject->isVisibleToUser(4, 1));
    }

    public function testDeleteById()
    {
        $this->assertFalse($this->subject->deleteById(9));
    }

    public function testUpdateById()
    {
        $this->assertFalse($this->subject->updateById(1, []));
    }

    public function testInsertReturnsProjectId()
    {
        $project = new Project();
        $project->name = 'Blackbox pentesting project';
        $project->creator_uid = 1;
        $project->visibility = 'public';
        $projectId = $this->subject->insert($project);
        $this->assertEquals(6, $projectId);
    }
}
