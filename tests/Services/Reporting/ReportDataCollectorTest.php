<?php declare(strict_types=1);

namespace Reconmap\Services\Reporting;

use League\CommonMark\GithubFlavoredMarkdownConverter;
use PHPUnit\Framework\TestCase;
use Reconmap\Models\ReportConfiguration;
use Reconmap\Repositories\ClientRepository;
use Reconmap\Repositories\ContactRepository;
use Reconmap\Repositories\OrganisationRepository;
use Reconmap\Repositories\ProjectRepository;
use Reconmap\Repositories\ReportConfigurationRepository;
use Reconmap\Repositories\ReportRepository;
use Reconmap\Repositories\TargetRepository;
use Reconmap\Repositories\TaskRepository;
use Reconmap\Repositories\UserRepository;
use Reconmap\Repositories\VulnerabilityRepository;

class ReportDataCollectorTest extends TestCase
{
    public function testDataCollectionForInvalidProject()
    {
        $projectRepository = $this->createMock(ProjectRepository::class);
        $reportRepository = $this->createMock(ReportRepository::class);
        $reportConfigurationRepository = $this->createMock(ReportConfigurationRepository::class);
        $vulnerabilityRepository = $this->createMock(VulnerabilityRepository::class);
        $organisationRepository = $this->createMock(OrganisationRepository::class);
        $userRepository = $this->createMock(UserRepository::class);
        $clientRepository = $this->createMock(ClientRepository::class);
        $taskRepository = $this->createMock(TaskRepository::class);
        $targetRepository = $this->createMock(TargetRepository::class);
        $contactRepository = $this->createMock(ContactRepository::class);

        $dataCollector = new ReportDataCollector($projectRepository, $reportRepository, $reportConfigurationRepository, $vulnerabilityRepository, $organisationRepository, $userRepository, $clientRepository, $taskRepository, $targetRepository, $contactRepository);
        $result = $dataCollector->collectForProject(0);
        $this->assertEquals([], $result);
    }

    public function testDataCollectionForValidProject()
    {
        $projectRepository = $this->createMock(ProjectRepository::class);
        $projectRepository->expects($this->once())
            ->method('findById')
            ->willReturn([]);

        $reportRepository = $this->createMock(ReportRepository::class);

        $reportConfiguration = new ReportConfiguration();
        $reportConfigurationRepository = $this->createMock(ReportConfigurationRepository::class);
        $reportConfigurationRepository->expects($this->once())
            ->method('findByProjectId')
            ->willReturn($reportConfiguration);

        $vulnerabilityRepository = $this->createMock(VulnerabilityRepository::class);
        $organisationRepository = $this->createMock(OrganisationRepository::class);
        $userRepository = $this->createMock(UserRepository::class);
        $clientRepository = $this->createMock(ClientRepository::class);
        $taskRepository = $this->createMock(TaskRepository::class);
        $targetRepository = $this->createMock(TargetRepository::class);
        $contactRepository = $this->createMock(ContactRepository::class);

        $dataCollector = new ReportDataCollector($projectRepository, $reportRepository, $reportConfigurationRepository, $vulnerabilityRepository, $organisationRepository, $userRepository, $clientRepository, $taskRepository, $targetRepository, $contactRepository);
        $result = $dataCollector->collectForProject(0);

        $expectedResult = ['configuration' => $reportConfiguration,
            'project' => array(),
            'org' => null,
            'date' => date('Y-m-d'),
            'reports' => array(),
            'markdownParser' => new GithubFlavoredMarkdownConverter(),
            'client' => null,
            'targets' => array(),
            'tasks' => array(),
            'vulnerabilities' => array(),
            'findingsOverview' => [
                0 => ['severity' => 'low', 'count' => 0],
                1 => ['severity' => 'medium', 'count' => 0],
                2 => ['severity' => 'high', 'count' => 0],
                3 => ['severity' => 'critical', 'count' => 0],
            ],
            'users' => array(),
            'contacts' => [],
        ];
        $this->assertEquals($expectedResult, $result);
    }
}
