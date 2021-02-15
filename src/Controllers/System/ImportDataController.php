<?php declare(strict_types=1);

namespace Reconmap\Controllers\System;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditLogAction;
use Reconmap\Models\Project;
use Reconmap\Models\Task;
use Reconmap\Repositories\CommandRepository;
use Reconmap\Repositories\ProjectRepository;
use Reconmap\Repositories\ProjectUserRepository;
use Reconmap\Repositories\TaskRepository;
use Reconmap\Services\AuditLogService;

class ImportDataController extends Controller
{

    public function __invoke(ServerRequestInterface $request): array
    {
        $files = $request->getUploadedFiles();
        $importFile = $files['importFile'];
        $importJsonString = $importFile->getStream()->getContents();

        $userId = $request->getAttribute('userId');

        $response = [];

        $json = json_decode($importJsonString);
        foreach ($json as $entityType => $entities) {
            $methodName = 'import' . $entityType;
            if (method_exists($this, $methodName)) {
                $response[] = array_merge(['name' => $entityType], call_user_func([$this, $methodName], $userId, $entities));
            } else {
                $this->logger->warning("Trying to import invalid entity type: $entityType");
            }
        }

        $this->auditAction($userId);

        return $response;
    }

    private function importCommands(int $userId, array $commands): array
    {
        $response = [
            'count' => 0,
            'errors' => [],
        ];

        $commandRepository = new CommandRepository($this->db);
        foreach ($commands as $jsonCommand) {
            $jsonCommand->creator_uid = $userId;
            $commandRepository->insert($jsonCommand);

            $response['count']++;
        }

        return $response;
    }

    private function importProjects(int $userId, array $projects): array
    {
        $response = [
            'count' => 0,
            'errors' => [],
        ];

        $projectRepository = new ProjectRepository($this->db);
        $projectUserRepository = new ProjectUserRepository($this->db);
        $taskRepository = new TaskRepository($this->db);

        foreach ($projects as $jsonProject) {
            $project = new Project;
            $project->creator_uid = $userId;
            $project->name = $jsonProject->name;
            $project->description = $jsonProject->description;
            $project->isTemplate = (bool)$jsonProject->is_template;
            try {
                $projectId = $projectRepository->insert($project);

                $projectUserRepository->create($projectId, $userId);

                foreach ($jsonProject->tasks as $jsonTask) {
                    $task = new Task();
                    $task->creator_uid = $userId;
                    $task->project_id = $projectId;
                    $task->name = $jsonTask->name;
                    $task->description = $jsonTask->description;
                    $taskRepository->insert($task);
                }

                $response['count']++;
            } catch (\Exception $e) {
                $response['errors'][] = $e->getMessage();
            }
        }

        return $response;
    }

    private function auditAction(int $loggedInUserId): void
    {
        $auditLogService = new AuditLogService($this->db);
        $auditLogService->insert($loggedInUserId, AuditLogAction::DATA_IMPORTED);
    }
}
