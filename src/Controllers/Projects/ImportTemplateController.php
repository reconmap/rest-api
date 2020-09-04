<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\ProjectRepository;
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

		$params = $request->getParsedBody();
		$userId = $request->getAttribute('userId');

		$projectRepository = new ProjectRepository($this->db);

		$taskRepository = new TaskRepository($this->db);

		$xml = simplexml_load_string($importXml);
		foreach ($xml->projects->project as $project) {
			$projectId = $projectRepository->insert((string)$project->name, (string)$project->name);

			foreach($project->tasks->task as $task) {
				$taskRepository->insert($projectId, 'none', (string)$task->name, (string)$task->description);
			}
		}

		return ['success' => true];
	}
}
