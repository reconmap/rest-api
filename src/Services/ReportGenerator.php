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
    public function __construct(
        private ProjectRepository             $projectRepository,
        private ReportRepository              $reportRepository,
        private ReportConfigurationRepository $reportConfigurationRepository,
        private VulnerabilityRepository       $vulnerabilityRepository,
        private OrganisationRepository        $organisationRepository,
        private UserRepository                $userRepository,
        private ClientRepository              $clientRepository,
        private TaskRepository                $taskRepository,
        private TargetRepository              $targetRepository,
        private TemplateEngine                $templateEngine)
    {
    }

    public function generate(int $projectId): array
    {
        $project = $this->projectRepository->findById($projectId);

        $configuration = $this->reportConfigurationRepository->findByProjectId($projectId);

        $vulnerabilities = $this->vulnerabilityRepository->findByProjectId($projectId);

        $reports = $this->reportRepository->findByProjectId($projectId);

        $markdownParser = new \Parsedown();

        $organisation = $this->organisationRepository->findRootOrganisation();

        $vars = [
            'configuration' => $configuration,
            'project' => $project,
            'org' => $organisation,
            'date' => date('Y-m-d'),
            'reports' => $reports,
            'markdownParser' => $markdownParser,
            'client' => $project['client_id'] ? $this->clientRepository->findById($project['client_id']) : null,
            'targets' => $this->targetRepository->findByProjectId($projectId),
            'tasks' => $this->taskRepository->findByProjectId($projectId),
            'vulnerabilities' => $vulnerabilities,
            'findingsOverview' => $this->createFindingsOverview($vulnerabilities),
        ];

        if (!empty($reports)) {
            $latestVersion = $reports[0];
            $vars['version'] = $latestVersion['version_name'];
        }

        $users = $this->userRepository->findByProjectId($projectId);
        foreach ($users as &$user) {
            $user['email_md5'] = md5($user['email']);
        }
        $vars['users'] = $users;

        $components = [
            'body' => $this->templateEngine->render('reports/body', $vars)
        ];

        if ($configuration->include_cover === 'default') {
            $components['cover'] = $this->templateEngine->render('reports/cover', $vars);
        } elseif ($configuration->include_cover === 'custom') {
            $components['cover'] = $this->templateEngine->renderString($configuration->custom_cover, $vars);
        }
        if ($configuration->include_header === 'default') {
            $components['header'] = $this->templateEngine->render('reports/header', $vars);
        } elseif ($configuration->include_header === 'custom') {
            $components['header'] = $this->templateEngine->renderString($configuration->custom_header, $vars);
        }
        if ($configuration->include_footer === 'default') {
            $components['footer'] = $this->templateEngine->render('reports/footer', $vars);
        } elseif ($configuration->include_footer === 'custom') {
            $components['footer'] = $this->templateEngine->renderString($configuration->custom_footer, $vars);
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
