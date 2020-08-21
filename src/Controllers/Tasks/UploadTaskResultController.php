<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Processors\NmapResultsProcessor;
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

		$processor = new NmapResultsProcessor($resultFile->getStream()->getMetadata()['uri']);
		$openPorts = $processor->parseOpenPorts();

		$targetId = null;
		$vulnRepository = new VulnerabilityRepository($this->db);
		foreach($openPorts as $openPort) {
			$vulnRepository->insert($task['project_id'], $targetId, $userId, 'Open port', "Port ${openPort} is open", 'medium');
		}

		$response = new \GuzzleHttp\Psr7\Response;
		$response->getBody()->write(json_encode($user));
		return $response->withHeader('Access-Control-Allow-Origin', '*');
	}
}
