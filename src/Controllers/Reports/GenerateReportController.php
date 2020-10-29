<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Reports;

use Dompdf\Dompdf;
use GuzzleHttp\Psr7\Response;
use Laminas\Diactoros\CallbackStream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\ClientRepository;
use Reconmap\Repositories\ProjectRepository;
use Reconmap\Repositories\ReportRepository;
use Reconmap\Repositories\ReportVersionRepository;
use Reconmap\Repositories\TargetRepository;
use Reconmap\Repositories\TaskRepository;
use Reconmap\Repositories\UserRepository;
use Reconmap\Repositories\VulnerabilityRepository;
use Reconmap\Services\Config;
use Reconmap\Services\ConfigConsumer;

class GenerateReportController extends Controller implements ConfigConsumer
{
    private ?Config $config = null;

    public function setConfig(Config $config): void
    {
        $this->config = $config;
    }

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
     * @param int $projectId
     * @return string
     */
    public function createHtml(int $projectId): string
    {
        $project = (new ProjectRepository($this->db))->findById($projectId);

        $vulnerabilities = (new VulnerabilityRepository($this->db))
            ->findByProjectId($projectId);

        $versions = (new ReportVersionRepository($this->db))->findByProjectId($projectId);
        $latestVersion = $versions[0];

        return $this->template->render('projects/report', [
            'project' => $project,
            'version' => $latestVersion['name'],
            'date' => date('Y-m-d'),
            'versions' => $versions,
            'client' => (new ClientRepository($this->db))->findById($project['client_id']),
            'users' => (new UserRepository($this->db))->findByProjectId($projectId),
            'targets' => (new TargetRepository($this->db))->findByProjectId($projectId),
            'tasks' => (new TaskRepository($this->db))->findByProjectId($projectId),
            'vulnerabilities' => $vulnerabilities,
            'findingsOverview' => $this->createFindingsOverview($vulnerabilities),
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
            $response = $this->createHtmlFormatResponse($filename, $html);
        } else {
            $response = $this->createPdfFormatResponse($html, $filename);
        }

        return $response;
    }

    /**
     * @param string $filename
     * @param string $html
     * @return Response
     */
    public function createHtmlFormatResponse(string $filename, string $html): Response
    {
        file_put_contents($this->config->getSetting('appDir') . '/data/' . $filename, $html);

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
        file_put_contents($this->config->getSetting('appDir') . '/data/' . $filename, $dompdf->output());

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
