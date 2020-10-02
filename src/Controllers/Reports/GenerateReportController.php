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

        $html = $this->createHtml($id);

        $reportId = (new ReportRepository($this->db))->insert($id, $userId, $format);

        return $this->createResponse($reportId, $format, $html);
    }


    /**
     * @param int $id
     * @return string
     */
    public function createHtml(int $id): string
    {
        $vulnerabilities = (new VulnerabilityRepository($this->db))->findByProjectId($id);
        return $this->template->render('projects/report', [
            'project' => (new ProjectRepository($this->db))->findById($id),
            'version' => '1.0',
            'date' => date('Y-m-d'),
            'users' => (new UserRepository($this->db))->findByProjectId($id),
            'targets' => (new TargetRepository($this->db))->findByProjectId($id),
            'tasks' => (new TaskRepository($this->db))->findByProjectId($id),
            'vulnerabilities' => $vulnerabilities,
            'findingsOverview' => $this->createFindingsOverview($vulnerabilities)
        ]);
    }


    /**
     * @param array $vulnerabilities
     * @return array[]
     */
    public function createFindingsOverview(array $vulnerabilities): array
    {
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
        return $findingsOverview;
    }


    /**
     * @param int $reportId
     * @param string $format
     * @param string $html
     * @return Response
     */
    public function createResponse(int $reportId, string $format, string $html): Response
    {
        $filename = sprintf("report-%d.%s", $reportId, $format);

        if ($format === 'html') {
            return $this->createHtmlFormatResponse($filename, $html);
        }
        return $this->createPdfFormatResponse($html, $filename);
    }


    /**
     * @param string $filename
     * @param string $html
     * @return Response
     */
    public function createHtmlFormatResponse(string $filename, string $html): Response
    {
        file_put_contents(RECONMAP_APP_DIR . '/data/' . $filename, $html);

        $response = new Response;
        $response->getBody()->write($html);
        return $response
            ->withHeader('Content-type', 'text/html');
    }


    /**
     * @param string $html
     * @param string $filename
     * @return Response
     */
    public function createPdfFormatResponse(string $html, string $filename): Response
    {
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);

        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        file_put_contents(RECONMAP_APP_DIR . '/data/' . $filename, $dompdf->output());

        $body = new CallbackStream(function () use ($dompdf) {
            return $dompdf->output();
        });
        $fileName = 'report.pdf';

        $response = new Response;
        return $response
            ->withBody($body)
            ->withHeader('Access-Control-Expose-Headers', 'Content-Disposition')
            ->withHeader('Content-Disposition', 'attachment; filename="' . $fileName . '";')
            ->withHeader('Content-type', 'application/pdf');
    }
}
