<?php declare(strict_types=1);

namespace Reconmap\Services\Reporting;

use PHPUnit\Framework\TestCase;
use Reconmap\Repositories\AttachmentRepository;
use Reconmap\Repositories\ClientRepository;
use Reconmap\Repositories\ContactRepository;
use Reconmap\Repositories\ProjectRepository;
use Reconmap\Repositories\ReportRepository;
use Reconmap\Repositories\TargetRepository;
use Reconmap\Repositories\TaskRepository;
use Reconmap\Repositories\UserRepository;
use Reconmap\Repositories\VulnerabilityRepository;
use Reconmap\Services\Filesystem\AttachmentFilePath;

class ReportDataCollectorTest extends TestCase
{
    public function testDataCollectionForInvalidProject()
    {
        $projectRepository = $this->createMock(ProjectRepository::class);
        $reportRepository = $this->createMock(ReportRepository::class);
        $vulnerabilityRepository = $this->createMock(VulnerabilityRepository::class);
        $userRepository = $this->createMock(UserRepository::class);
        $clientRepository = $this->createMock(ClientRepository::class);
        $taskRepository = $this->createMock(TaskRepository::class);
        $targetRepository = $this->createMock(TargetRepository::class);
        $contactRepository = $this->createMock(ContactRepository::class);
        $attachmentRepository = $this->createMock(AttachmentRepository::class);
        $attachmentFilePath = $this->createMock(AttachmentFilePath::class);

        $dataCollector = new ReportDataCollector($projectRepository, $reportRepository, $vulnerabilityRepository,
            $userRepository, $clientRepository, $taskRepository, $targetRepository, $contactRepository, $attachmentRepository, $attachmentFilePath);
        $result = $dataCollector->collectForProject(0);
        $this->assertEquals([], $result);
    }

    public function testDataCollectionForValidProject()
    {
        $projectRepository = $this->createMock(ProjectRepository::class);
        $projectRepository->expects($this->once())
            ->method('findById')
            ->willReturn(['service_provider_id' => 1]);

        $reportRepository = $this->createMock(ReportRepository::class);
        $vulnerabilityRepository = $this->createMock(VulnerabilityRepository::class);
        $userRepository = $this->createMock(UserRepository::class);
        $clientRepository = $this->createMock(ClientRepository::class);
        $taskRepository = $this->createMock(TaskRepository::class);
        $targetRepository = $this->createMock(TargetRepository::class);
        $contactRepository = $this->createMock(ContactRepository::class);
        $attachmentRepository = $this->createMock(AttachmentRepository::class);
        $attachmentFilePath = $this->createMock(AttachmentFilePath::class);

        $dataCollector = new ReportDataCollector($projectRepository, $reportRepository, $vulnerabilityRepository,
            $userRepository, $clientRepository, $taskRepository, $targetRepository, $contactRepository, $attachmentRepository, $attachmentFilePath);
        $result = $dataCollector->collectForProject(0);

        $expectedResult = [
            'date' => date('Y-m-d'),
            'revisions' => array(),
            'project' => array(
                'attachments' => array(),
                'service_provider_id' => 1
            ),
            'serviceProvider' => null,
            'client' => null,
            'assets' => array(),
            'tasks' => array(),
            'findings' => [
                'list' => array(),
                'stats' => [
                    0 => ['severity' => 'low', 'count' => 0],
                    1 => ['severity' => 'medium', 'count' => 0],
                    2 => ['severity' => 'high', 'count' => 0],
                    3 => ['severity' => 'critical', 'count' => 0],
                ]],
            'users' => array(),
            'lastRevisionName' => ''
        ];
        $this->assertEquals($expectedResult, $result);
    }
}
