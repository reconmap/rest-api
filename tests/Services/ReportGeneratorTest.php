<?php declare(strict_types=1);

namespace Reconmap\Services;

use PHPUnit\Framework\TestCase;
use Reconmap\Repositories\ClientRepository;
use Reconmap\Repositories\OrganisationRepository;
use Reconmap\Repositories\ProjectRepository;
use Reconmap\Repositories\ReportConfigurationRepository;
use Reconmap\Repositories\ReportRepository;
use Reconmap\Repositories\TargetRepository;
use Reconmap\Repositories\TaskRepository;
use Reconmap\Repositories\UserRepository;
use Reconmap\Repositories\VulnerabilityRepository;

class ReportGeneratorTest extends TestCase
{
    public function testHappyPath()
    {
        $fakeProject = ['client_id' => 5];

        $mockProjectRepository = $this->createMock(ProjectRepository::class);
        $mockProjectRepository->expects($this->once())
            ->method('findById')
            ->willReturn($fakeProject);

        $mockReportRepository = $this->createMock(ReportRepository::class);
        $mockReportConfigurationRepository = $this->createMock(ReportConfigurationRepository::class);
        $mockVulnerabilityRepository = $this->createMock(VulnerabilityRepository::class);
        $mockOrgRepository = $this->createMock(OrganisationRepository::class);
        $mockUserRepository = $this->createMock(UserRepository::class);
        $mockClientRepository = $this->createMock(ClientRepository::class);
        $mockTaskRepository = $this->createMock(TaskRepository::class);
        $mockTargetRepository = $this->createMock(TargetRepository::class);
        $mockTemplateEngine = $this->createMock(TemplateEngine::class);

        $subject = new ReportGenerator($mockProjectRepository, $mockReportRepository, $mockReportConfigurationRepository, $mockVulnerabilityRepository, $mockOrgRepository,
            $mockUserRepository, $mockClientRepository, $mockTaskRepository, $mockTargetRepository, $mockTemplateEngine);
        $report = $subject->generate(1);

        $expectedReport = [
            'body' => '',
            'cover' => '',
            'header' => '',
            'footer' => ''
        ];
        $this->assertEquals($expectedReport, $report);
    }
}
