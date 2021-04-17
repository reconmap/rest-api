<?php declare(strict_types=1);

namespace Reconmap\Repositories\Importers;

use PHPUnit\Framework\TestCase;
use Reconmap\Repositories\ProjectRepository;
use Reconmap\Repositories\ProjectUserRepository;
use Reconmap\Repositories\TaskRepository;

class ProjectsImporterTest extends TestCase
{
    public function testEmptyProjectsImport()
    {
        $fakeUserId = 59;
        $projects = [];

        $mockProjectRepository = $this->createMock(ProjectRepository::class);
        $mockProjectUserRepository = $this->createMock(ProjectUserRepository::class);
        $mockTaskRepository = $this->createMock(TaskRepository::class);

        $subject = new ProjectsImporter($mockProjectRepository, $mockProjectUserRepository, $mockTaskRepository);
        $result = $subject->import($fakeUserId, $projects);

        $this->assertEquals(['count' => 0, 'errors' => []], $result);
    }

    public function testSuccessfulImport()
    {
        $fakeUserId = 59;
        $projects = [
            (object)[
                'name' => 'External project',
                'description' => 'No description',
                'is_template' => false,
                'tasks' => [],
            ],
            (object)[
                'name' => 'External template',
                'description' => 'No description',
                'is_template' => true,
                'tasks' => [
                    (object)[
                        'summary' => 'Do X',
                        'description' => 'Do X or else'
                    ]
                ],
            ]
        ];

        $mockProjectRepository = $this->createMock(ProjectRepository::class);
        $mockProjectRepository->expects($this->exactly(2))
            ->method('insert')
            ->willReturnOnConsecutiveCalls(100, 101);

        $mockProjectUserRepository = $this->createMock(ProjectUserRepository::class);
        $mockProjectUserRepository->expects($this->exactly(2))
            ->method('create')
            ->withConsecutive([100, $fakeUserId], [101, $fakeUserId]);

        $mockTaskRepository = $this->createMock(TaskRepository::class);
        $mockTaskRepository->expects($this->once())
            ->method('insert');

        $subject = new ProjectsImporter($mockProjectRepository, $mockProjectUserRepository, $mockTaskRepository);
        $result = $subject->import($fakeUserId, $projects);

        $this->assertEquals(['count' => 2, 'errors' => []], $result);
    }
}
