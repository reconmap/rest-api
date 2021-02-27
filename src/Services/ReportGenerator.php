<?php declare(strict_types=1);

namespace Reconmap\Services;

use Reconmap\Repositories\ClientRepository;
use Reconmap\Repositories\OrganisationRepository;
use Reconmap\Repositories\ProjectRepository;
use Reconmap\Repositories\ReportConfigurationRepository;
use Reconmap\Repositories\ReportRepository;
use Reconmap\Repositories\TargetRepository;
use Reconmap\Repositories\TaskRepository;
use Reconmap\Repositories\UserRepository;
use Reconmap\Repositories\VulnerabilityRepository;

class ReportGenerator
{
    private ApplicationConfig $config;
    private \mysqli $db;
    private TemplateEngine $template;

    public function __construct(ApplicationConfig $config, \mysqli $db, TemplateEngine $template)
    {
        $this->config = $config;
        $this->db = $db;
        $this->template = $template;
    }

    public function generate(int $projectId): array
    {
        $project = (new ProjectRepository($this->db))->findById($projectId);

        $configurationRepository = new ReportConfigurationRepository($this->db);
        $configuration = $configurationRepository->findByProjectId($projectId);

        $vulnerabilities = (new VulnerabilityRepository($this->db))
            ->findByProjectId($projectId);

        $reports = (new ReportRepository($this->db))->findByProjectId($projectId);
        $latestVersion = $reports[0];

        $markdownParser = new \Parsedown();

        $organisation = (new OrganisationRepository($this->db))->findRootOrganisation();

        $vars = [
            'configuration' => $configuration,
            'project' => $project,
            'org' => $organisation,
            'version' => $latestVersion['version_name'],
            'date' => date('Y-m-d'),
            'reports' => $reports,
            'markdownParser' => $markdownParser,
            'client' => $project['client_id'] ? (new ClientRepository($this->db))->findById($project['client_id']) : null,
            'targets' => (new TargetRepository($this->db))->findByProjectId($projectId),
            'tasks' => (new TaskRepository($this->db))->findByProjectId($projectId),
            'vulnerabilities' => $vulnerabilities,
            'findingsOverview' => $this->createFindingsOverview($vulnerabilities),
        ];

        $users = (new UserRepository($this->db))->findByProjectId($projectId);
        foreach ($users as &$user) {
            $user['email_md5'] = md5($user['email']);
        }
        $vars['users'] = $users;


        $components = [
            'body' => $this->template->render('reports/body', $vars)
        ];

        if ($configuration->include_cover === 'default') {
            $components['cover'] = $this->template->render('reports/cover', $vars);
        } elseif ($configuration->include_cover === 'custom') {
            $components['cover'] = $this->template->renderString($configuration->custom_cover, $vars);
        }
        if ($configuration->include_header === 'default') {
            $components['header'] = $this->template->render('reports/header', $vars);
        } elseif ($configuration->include_header === 'custom') {
            $components['header'] = $this->template->renderString($configuration->custom_header, $vars);
        }
        if ($configuration->include_footer === 'default') {
            $components['footer'] = $this->template->render('reports/footer', $vars);
        } elseif ($configuration->include_footer === 'custom') {
            $components['footer'] = $this->template->renderString($configuration->custom_footer, $vars);
        }

        return $components;
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
