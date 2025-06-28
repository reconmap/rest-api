<?php declare(strict_types=1);

namespace Reconmap\Services\Reporting;

use Reconmap\Repositories\AttachmentRepository;
use Reconmap\Repositories\ClientRepository;
use Reconmap\Repositories\ContactRepository;
use Reconmap\Repositories\ProjectRepository;
use Reconmap\Repositories\ReportRepository;
use Reconmap\Repositories\SearchCriterias\TargetSearchCriteria;
use Reconmap\Repositories\SearchCriterias\VulnerabilitySearchCriteria;
use Reconmap\Repositories\TargetRepository;
use Reconmap\Repositories\TaskRepository;
use Reconmap\Repositories\UserRepository;
use Reconmap\Repositories\VulnerabilityRepository;
use Reconmap\Services\Filesystem\AttachmentFilePath;

readonly class ReportDataCollector
{
    public function __construct(
        private ProjectRepository       $projectRepository,
        private ReportRepository        $reportRepository,
        private VulnerabilityRepository $vulnerabilityRepository,
        private UserRepository          $userRepository,
        private ClientRepository        $clientRepository,
        private TaskRepository          $taskRepository,
        private TargetRepository        $targetRepository,
        private ContactRepository       $contactRepository,
        private AttachmentRepository    $attachmentRepository,
        private AttachmentFilePath      $attachmentFilePathService
    )
    {
    }

    public function collectForProject(int $projectId): array
    {
        $project = $this->projectRepository->findById($projectId);
        if (is_null($project)) {
            return [];
        }

        $vulnerabilitySearchCriteria = new VulnerabilitySearchCriteria();
        $vulnerabilitySearchCriteria->addProjectCriterion($projectId);
        $vulnerabilitySearchCriteria->addPublicVisibilityCriterion();

        $vulnerabilitySort = "FIELD(v.risk, 'critical', 'high', 'medium', 'low', 'none')";
        if (isset($project['vulnerability_metrics']) && (strcmp($project['vulnerability_metrics'], "OWASP_RR") === 0)) {
            $vulnerabilitySort = "FIELD(v.owasp_overall, 'critical','high','medium','low','note')";
        }
        $vulnerabilities = $this->vulnerabilityRepository->search($vulnerabilitySearchCriteria, null, $vulnerabilitySort);
        foreach ($vulnerabilities as $key => $vuln) {
            $pngs = $this->attachmentRepository->findByParentId('vulnerability', $vuln['id'], 'image/png');
            $jpegs = $this->attachmentRepository->findByParentId('vulnerability', $vuln['id'], 'image/jpeg');
            $gifs = $this->attachmentRepository->findByParentId('vulnerability', $vuln['id'], 'image/gif');
            $attachments = array_merge($pngs, $jpegs, $gifs);
            $att = [];
            foreach ($attachments as $k => $value) {
                $att[$k] = $this->attachmentFilePathService->generateFilePath($value['file_name']);
            }
            $vulnerabilities[$key]['attachments'] = $att;
        }
        $findingsForReport = [
            "stats" => $this->createFindingsOverview($vulnerabilities),
            "list" => $vulnerabilities
        ];

        $lastRevisionName = null;
        $reportRevisions = $this->reportRepository->findByProjectId($projectId);
        if (!empty($reportRevisions)) {
            $latestVersion = $reportRevisions[0];
            $lastRevisionName = $latestVersion['version_name'];
        }

        $serviceProvider = $this->loadDataForOrganisation($project['service_provider_id']);
        $client = $this->loadDataForOrganisation($project['client_id']);

        $targetSearchCriteria = new TargetSearchCriteria();
        $targetSearchCriteria->addProjectCriterion($projectId);
        $assets = $this->targetRepository->search($targetSearchCriteria);

        $pngs = $this->attachmentRepository->findByParentId('project', $projectId, 'image/png');
        $jpegs = $this->attachmentRepository->findByParentId('project', $projectId, 'image/jpeg');
        $gifs = $this->attachmentRepository->findByParentId('project', $projectId, 'image/gif');
        $attachments = array_merge($pngs, $jpegs, $gifs);
        $att = [];
        foreach ($attachments as $k => $value) {
            $att[$k] = $this->attachmentFilePathService->generateFilePath($value['file_name']);
        }
        $project['attachments'] = $att;

        $users = $this->userRepository->findByProjectId($projectId);

        $vars = [
            'date' => date('Y-m-d'),
            'revisions' => $reportRevisions,
            'lastRevisionName' => $lastRevisionName,
            'serviceProvider' => $serviceProvider,
            'client' => $client,
            'project' => $project,
            'users' => $users,
            'assets' => $assets,
            'tasks' => $this->taskRepository->findByProjectId($projectId),
            'findings' => $findingsForReport,
        ];

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

    private function loadDataForOrganisation(?int $organisationId): ?array
    {
        if (!$organisationId) {
            return null;
        }

        $org = $this->clientRepository->findById($organisationId);
        if ($org) {
            $orgForReport = (array)$org;
            if ($org->small_logo_attachment_id) {
                $orgForReport['smallLogo'] = $this->attachmentFilePathService->generateFilePath($this->attachmentRepository->getFileNameById($org->small_logo_attachment_id));
            }
            if ($org->logo_attachment_id) {
                $orgForReport['logo'] = $this->attachmentFilePathService->generateFilePath($this->attachmentRepository->getFileNameById($org->logo_attachment_id));
            }
            $orgForReport['contacts'] = $this->contactRepository->findByClientId($organisationId);

            return $orgForReport;
        }

        return null;
    }
}
