<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Reports;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\ProjectRepository;
use Reconmap\Repositories\TaskRepository;
use Reconmap\Repositories\VulnerabilityRepository;
use Dompdf\Dompdf;
use Laminas\Diactoros\CallbackStream;
use Reconmap\Repositories\ReportRepository;

class GenerateReportController extends Controller
{

	public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
	{
		$id = (int)$args['id'];
		$params = $request->getQueryParams();
		$format = $params['format'] ?? 'html';

		$userId = $request->getAttribute('userId');

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

		$reportRepository = new ReportRepository($this->db);
		$reportId = $reportRepository->insert($id, $userId, $format);

		$response = new \GuzzleHttp\Psr7\Response;

		$filename = sprintf("report-%d.%s", $reportId, $format);

		if ($format === 'html') {
			file_put_contents(RECONMAP_APP_DIR . '/' . $filename, $html);

			$response = new \GuzzleHttp\Psr7\Response;
			$response->getBody()->write($html);
			return $response->withHeader('Access-Control-Allow-Origin', '*')
				->withHeader('Content-type', 'text/html');
		} else {
			$dompdf = new Dompdf();
			$dompdf->loadHtml($html);

			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			file_put_contents(RECONMAP_APP_DIR . '/' . $filename, $dompdf->output());

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
