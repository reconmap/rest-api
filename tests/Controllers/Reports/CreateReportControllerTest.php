<?php declare(strict_types=1);

namespace Reconmap\Controllers\Reports;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Models\ReportConfiguration;
use Reconmap\Repositories\AttachmentRepository;
use Reconmap\Repositories\ProjectRepository;
use Reconmap\Repositories\ReportConfigurationRepository;
use Reconmap\Repositories\ReportRepository;
use Reconmap\Services\ApplicationConfig;
use Reconmap\Services\AttachmentFilePath;
use Reconmap\Services\ReportGenerator;

class CreateReportControllerTest extends TestCase
{
    public function testHappyPath()
    {
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->once())
            ->method('getAttribute')
            ->with('userId')
            ->willReturn(9);
        $mockRequest->expects($this->once())
            ->method('getBody')
            ->willReturn('{"projectId": 5, "name": "1.0", "description": "Draft"}');

        $mockProject = ['name' => 'A project'];

        $mockAttachmentFilePath = $this->createMock(AttachmentFilePath::class);
        $mockProjectRepository = $this->createMock(ProjectRepository::class);
        $mockProjectRepository->expects($this->once())
            ->method('findById')
            ->with(5)
            ->willReturn($mockProject);
        $mockReportRepository = $this->createMock(ReportRepository::class);

        $mockReportConfig = new ReportConfiguration();
        $mockReportConfig->include_cover = 'none';
        $mockReportConfig->include_header = 'none';
        $mockReportConfig->include_footer = 'none';

        $mockReportConfigurationRepository = $this->createMock(ReportConfigurationRepository::class);
        $mockReportConfigurationRepository->expects($this->once())
            ->method('findByProjectId')
            ->willReturn($mockReportConfig);

        $mockAttachmentRepository = $this->createMock(AttachmentRepository::class);

        $mockReportSections = ['body' => 'foo/bar'];
        $mockReportGenerator = $this->createMock(ReportGenerator::class);
        $mockReportGenerator->expects($this->once())
            ->method('generate')
            ->with(5)
            ->willReturn($mockReportSections);

        $mockApplicationConfig = $this->createMock(ApplicationConfig::class);
        $mockApplicationConfig->expects($this->once())
            ->method('getAppDir')
            ->willReturn(__DIR__);

        $controller = new CreateReportController($mockAttachmentFilePath, $mockProjectRepository, $mockReportRepository, $mockReportConfigurationRepository, $mockAttachmentRepository, $mockReportGenerator, $mockApplicationConfig);
        $response = $controller($mockRequest);

        $this->assertEquals([0, 0], $response);
    }
}
