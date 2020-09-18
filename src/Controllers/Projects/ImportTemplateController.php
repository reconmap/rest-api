<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\Project;
use Reconmap\Repositories\ProjectRepository;
use Reconmap\Repositories\ProjectUserRepository;
use Reconmap\Repositories\TaskRepository;

class ImportTemplateController extends Controller
{

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $files = $request->getUploadedFiles();
        $importFile = $files['importFile'];
        /*
        $resultFile->getClientFilename();
        $resultFile->getSize();
        $resultFile->getClientMediaType();
        $resultFile->moveTo('path');
        */
        $importXml = $importFile->getStream()->getContents();

        $userId = $request->getAttribute('userId');

        $projectRepository = new ProjectRepository($this->db);
        $projectUserRepository = new ProjectUserRepository($this->db);
        $taskRepository = new TaskRepository($this->db);

        $projectsImported = [];

        $xml = simplexml_load_string($importXml);
        foreach ($xml->projects->project as $xmlProject) {
            $project = new Project;
            $project->name = (string)$xmlProject->name;
            $project->description = (string)$xmlProject->description;
            $project->isTemplate = (bool)$xmlProject['template'];
            $projectId = $projectRepository->insert($project);

            $projectUserRepository->create($projectId, $userId);

            $projectsImported[] = $project;

            foreach ($xmlProject->tasks->task as $task) {
                $taskRepository->insert($projectId, 'none', (string)$task->name, (string)$task->description);
            }
        }

        return ['projectsImported' => $projectsImported];
    }
}
