<?php declare(strict_types=1);

namespace Reconmap\Services;

use Reconmap\Repositories\ClientRepository;
use Reconmap\Repositories\OrganisationRepository;
use Reconmap\Repositories\ProjectRepository;
use Reconmap\Repositories\ReportRepository;
use Reconmap\Repositories\TargetRepository;
use Reconmap\Repositories\TaskRepository;
use Reconmap\Repositories\UserRepository;
use Reconmap\Repositories\VulnerabilityRepository;

class ReportGenerator
{
    private Config $config;
    private \mysqli $db;
    private TemplateEngine $template;

    public function __construct(Config $config, \mysqli $db, TemplateEngine $template)
    {
        $this->config = $config;
        $this->db = $db;
        $this->template = $template;
    }

    public function generate(int $projectId): string
    {
        $project = (new ProjectRepository($this->db))->findById($projectId);

        $vulnerabilities = (new VulnerabilityRepository($this->db))
            ->findByProjectId($projectId);

        $reports = (new ReportRepository($this->db))->findByProjectId($projectId);
        $latestVersion = $reports[0];

        $organisation = (new OrganisationRepository($this->db))->findRootOrganisation();

        return $this->template->render('projects/report', [
            'project' => $project,
            'org' => $organisation,
            'version' => $latestVersion['name'],
            'date' => date('Y-m-d'),
            'reports' => $reports,
            'client' => (new ClientRepository($this->db))->findById($project['client_id']),
            'users' => (new UserRepository($this->db))->findByProjectId($projectId),
            'targets' => (new TargetRepository($this->db))->findByProjectId($projectId),
            'tasks' => (new TaskRepository($this->db))->findByProjectId($projectId),
            'vulnerabilities' => $vulnerabilities,
            'findingsOverview' => $this->createFindingsOverview($vulnerabilities),
        ]);
    }

    private function createFindingsOverview(array $vulnerabilities): array
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
}
