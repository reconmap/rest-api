<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Reports;

use Dompdf\Dompdf;
use GuzzleHttp\Psr7\Response;
use Laminas\Diactoros\CallbackStream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\ProjectRepository;
use Reconmap\Repositories\ReportRepository;
use Reconmap\Repositories\TargetRepository;
use Reconmap\Repositories\TaskRepository;
use Reconmap\Repositories\UserRepository;
use Reconmap\Repositories\VulnerabilityRepository;

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

        $usersRepository = new UserRepository($this->db);
        $users = $usersRepository->findByProjectId($id);

        $targetsRepository = new TargetRepository($this->db);
        $targets = $targetsRepository->findByProjectId($id);

        $vulnerabilityRepository = new VulnerabilityRepository($this->db);
        $vulnerabilities = $vulnerabilityRepository->findByProjectId($id);

        $findingsOverview = array_map(function (string $severity) use ($vulnerabilities) {
            return [
                'severity' => $severity,
                'count' => array_reduce($vulnerabilities, function (int $carry, array $item) use ($severity) {
                    return $carry + ($item['risk'] == $severity ? 1 : 0);
                }, 0)
            ];
        }, ['low', 'medium', 'high', 'critical']);
        usort($findingsOverview, function ($a, $b) {
            return $b['count'] <=> $a['count'];
        });

        $html = $this->template->render('projects/report', [
            'project' => $project,
            'version' => '1.0',
            'date' => $date,
            'users' => $users,
            'targets' => $targets,
            'tasks' => $tasks,
            'vulnerabilities' => $vulnerabilities,
            'findingsOverview' => $findingsOverview
        ]);

        $reportRepository = new ReportRepository($this->db);
        $reportId = $reportRepository->insert($id, $userId, $format);

        $response = new Response;

        $filename = sprintf("report-%d.%s", $reportId, $format);

        if ($format === 'html') {
            file_put_contents(RECONMAP_APP_DIR . '/data/' . $filename, $html);

            $response = new Response;
            $response->getBody()->write($html);
            return $response
                ->withHeader('Content-type', 'text/html');
        } else {
            $dompdf = new Dompdf();
            $dompdf->loadHtml($html);

            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            file_put_contents(RECONMAP_APP_DIR . '/data/' . $filename, $dompdf->output());

            $body = new CallbackStream(function () use ($dompdf) {
                return $dompdf->output();
            });
            $fileName = 'report.pdf';
            return $response
                ->withBody($body)
                ->withHeader('Access-Control-Expose-Headers', 'Content-Disposition')
                ->withHeader('Content-Disposition', 'attachment; filename="' . $fileName . '";')
                ->withHeader('Content-type', 'application/pdf');
        }
    }
}
