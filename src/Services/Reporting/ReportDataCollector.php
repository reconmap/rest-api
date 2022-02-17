<?php declare(strict_types=1);

namespace Reconmap\Services\Reporting;

use League\CommonMark\GithubFlavoredMarkdownConverter;
use Reconmap\Repositories\ClientRepository;
use Reconmap\Repositories\OrganisationRepository;
use Reconmap\Repositories\ProjectRepository;
use Reconmap\Repositories\ReportConfigurationRepository;
use Reconmap\Repositories\ReportRepository;
use Reconmap\Repositories\SearchCriterias\TargetSearchCriteria;
use Reconmap\Repositories\SearchCriterias\VulnerabilitySearchCriteria;
use Reconmap\Repositories\TargetRepository;
use Reconmap\Repositories\TaskRepository;
use Reconmap\Repositories\UserRepository;
use Reconmap\Repositories\VulnerabilityRepository;

class ReportDataCollector
{
    public function __construct(
        private readonly ProjectRepository $projectRepository,
        private readonly ReportRepository $reportRepository,
        private readonly ReportConfigurationRepository $reportConfigurationRepository,
        private readonly VulnerabilityRepository $vulnerabilityRepository,
        private readonly OrganisationRepository $organisationRepository,
        private readonly UserRepository $userRepository,
        private readonly ClientRepository $clientRepository,
        private readonly TaskRepository $taskRepository,
        private readonly TargetRepository $targetRepository)
    {
    }

    public function collectForProject(int $projectId): array
    {
        $project = $this->projectRepository->findById($projectId);
        if (is_null($project)) {
            return [];
        }

        $configuration = $this->reportConfigurationRepository->findByProjectId($projectId);

        $vulnerabilitySearchCriteria = new VulnerabilitySearchCriteria();
        $vulnerabilitySearchCriteria->addProjectCriterion($projectId);
        $vulnerabilitySearchCriteria->addPublicVisibilityCriterion();
        $vulnerabilities = $this->vulnerabilityRepository->search($vulnerabilitySearchCriteria);

        $reports = $this->reportRepository->findByProjectId($projectId);

        $markdownParser = new GithubFlavoredMarkdownConverter();

        $organisation = $this->organisationRepository->findRootOrganisation();

        $targetSearchCriteria = new TargetSearchCriteria();
        $targetSearchCriteria->addProjectCriterion($projectId);
        $targets = $this->targetRepository->search($targetSearchCriteria);

        $vars = [
            'configuration' => $configuration,
            'project' => $project,
            'org' => $organisation,
            'date' => date('Y-m-d'),
            'reports' => $reports,
            'markdownParser' => $markdownParser,
            'client' => isset($project['client_id']) ? $this->clientRepository->findById($project['client_id']) : null,
            'targets' => $targets,
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

        return $vars;
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
