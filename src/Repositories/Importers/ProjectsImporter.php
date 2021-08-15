<?php declare(strict_types=1);

namespace Reconmap\Repositories\Importers;

use Reconmap\Models\Project;
use Reconmap\Models\Task;
use Reconmap\Repositories\ProjectRepository;
use Reconmap\Repositories\ProjectUserRepository;
use Reconmap\Repositories\TaskRepository;

class ProjectsImporter implements Importable
{
    public function __construct(private ProjectRepository     $projectRepository,
                                private ProjectUserRepository $projectUserRepository,
                                private TaskRepository        $taskRepository)
    {
    }

    public function import(int $userId, array $projects): array
    {
        $response = [
            'count' => 0,
            'errors' => [],
        ];

        foreach ($projects as $jsonProject) {
            $project = new Project;
            $project->creator_uid = $userId;
            $project->name = $jsonProject->name;
            $project->description = $jsonProject->description;
            $project->is_template = (bool)$jsonProject->is_template;
            try {
                $projectId = $this->projectRepository->insert($project);

                $this->projectUserRepository->create($projectId, $userId);

                foreach ($jsonProject->tasks as $jsonTask) {
                    $task = new Task();
                    $task->creator_uid = $userId;
                    $task->project_id = $projectId;
                    $task->summary = $jsonTask->summary;
                    $task->description = $jsonTask->description;
                    $this->taskRepository->insert($task);
                }

                $response['count']++;
            } catch (\Exception $e) {
                $response['errors'][] = $e->getMessage();
            }
        }

        return $response;
    }
}
