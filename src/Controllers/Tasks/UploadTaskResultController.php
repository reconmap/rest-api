<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Processors\ProcessorFactory;
use Reconmap\Repositories\TaskRepository;
use Reconmap\Repositories\TaskResultRepository;
use Reconmap\Repositories\VulnerabilityRepository;

class UploadTaskResultController extends Controller
{

	public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
	{
		$files = $request->getUploadedFiles();
		$resultFile = $files['resultFile'];
		/*
		$resultFile->getClientFilename();
		$resultFile->getSize();
		$resultFile->getClientMediaType();
		$resultFile->moveTo('path');
		*/
		$output = $resultFile->getStream()->getContents();

		$params = $request->getParsedBody();
		$taskId = (int)$params['taskId'];

		$userId = $request->getAttribute('userId');

		$taskRepo = new TaskRepository($this->db);
		$task = $taskRepo->findById($taskId);

		$repository = new TaskResultRepository($this->db);
		$user = $repository->insert($taskId, $userId, $output);

		$targetId = null;
		$vulnRepository = new VulnerabilityRepository($this->db);

		$path = $resultFile->getStream()->getMetadata()['uri'];
		$processorFactory = new ProcessorFactory;
		$processor = $processorFactory->createByTaskType($task['parser']);
		if ($processor) {
			$vulnerabilities = $processor->parseVulnerabilities($path);

			foreach ($vulnerabilities as $vulnerability) {
				$vulnRepository->insert($task['project_id'], $targetId, $userId, $vulnerability->summary, $vulnerability->description, 'medium');
			}
		}

		$response = new \GuzzleHttp\Psr7\Response;
		$response->getBody()->write(json_encode($user));
		return $response->withHeader('Access-Control-Allow-Origin', '*');
	}
}
