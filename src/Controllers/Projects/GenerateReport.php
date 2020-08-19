<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\ProjectRepository;
use Reconmap\Repositories\TaskRepository;
use Reconmap\Repositories\VulnerabilityRepository;
use Dompdf\Dompdf;
use Laminas\Diactoros\CallbackStream;

class GenerateReport extends Controller
{

	public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
	{
		$id = (int)$args['id'];
		$params = $request->getQueryParams();
		$format = $params['format'] ?? 'html';

		$repository = new ProjectRepository($this->db);
		$project = $repository->findById($id);

		$date = date('Y-m-d');

		$taskRepository = new TaskRepository($this->db);
		$tasks = $taskRepository->findByProjectId($id);

		$vulnerabilityRepository = new VulnerabilityRepository($this->db);
		$vulnerabilities = $vulnerabilityRepository->findByProjectId($id);

		$html = $this->template->render('projects/report', [
			'project' => $project,
			'date' => $date,
			'tasks' => $tasks,
			'vulnerabilities' => $vulnerabilities
		]);

		$response = new \GuzzleHttp\Psr7\Response;

		if ($format === 'html') {
			$response = new \GuzzleHttp\Psr7\Response;
			$response->getBody()->write($html);
			return $response->withHeader('Access-Control-Allow-Origin', '*')
				->withHeader('Content-type', 'text/html');
		} else {
			$dompdf = new Dompdf();
			$dompdf->loadHtml($html);

			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$body = new CallbackStream(function () use ($dompdf) {
				$dompdf->stream();
			});
			$fileName = 'report.pdf';
			return $response
				->withBody($body)
				->withHeader('Access-Control-Expose-Headers', 'Content-Disposition')
				->withHeader('Access-Control-Allow-Origin', '*')
				->withHeader('Content-Disposition', 'attachment; filename="' . $fileName . '";')
				->withHeader('Content-type', 'application/pdf');
		}
	}
}
