<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Ponup\SqlBuilders\SearchCriteria;
use Reconmap\DatabaseTestCase;
use Reconmap\Models\Project;

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
        $projectId = 2;

        $expectedProject = [
            'id' => $projectId,
            'update_ts' => null,
            'creator_uid' => 1,
            'client_id' => null,
            'is_template' => 1,
            'visibility' => 'public',
            'name' => 'Linux host template',
            'description' => 'Project template to show general linux host reconnaissance tasks',
            'category_id' => null,
            'category_name' => null,
            'engagement_start_date' => null,
            'engagement_end_date' => null,
            'archived' => 0,
            'archive_ts' => null,
            'client_name' => null,
            'creator_full_name' => 'Jane Doe',
            'external_id' => null,
            'vulnerability_metrics' => null,
            'num_tasks' => 3,
        ];

        $project1 = $this->subject->findById($projectId);
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
        $this->assertTrue($this->subject->isVisibleToUser(4, 1));
    }

    public function testIsNotVisibleToUser()
    {
        $this->assertFalse($this->subject->isVisibleToUser(5, 1));
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
        $this->assertEquals(7, $projectId);
    }
}
