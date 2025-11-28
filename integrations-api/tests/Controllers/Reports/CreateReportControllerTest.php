<?php declare(strict_types=1);

namespace Reconmap\Controllers\Reports;

use GuzzleHttp\Psr7\Utils;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Repositories\AttachmentRepository;
use Reconmap\Repositories\ProjectRepository;
use Reconmap\Repositories\ReportRepository;
use Reconmap\Services\Filesystem\AttachmentFilePath;
use Reconmap\Services\Reporting\ReportDataCollector;

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
            ->willReturn(Utils::streamFor('{"projectId": 5, "reportTemplateId": 1, "name": "1.0", "description": "Draft"}'));

        $mockProject = ['name' => 'A project'];

        $mockAttachmentFilePath = $this->createMock(AttachmentFilePath::class);
        $mockAttachmentFilePath->expects($this->once())
            ->method('generateFilePathFromAttachment')
            ->willReturn(dirname(__DIR__, 3) . '/data/attachments/default-report-template.docx');

        $mockProjectRepository = $this->createMock(ProjectRepository::class);
        $mockProjectRepository->expects($this->once())
            ->method('findById')
            ->with(5)
            ->willReturn($mockProject);
        $mockReportRepository = $this->createMock(ReportRepository::class);

        $mockAttachmentRepository = $this->createMock(AttachmentRepository::class);
        $mockAttachmentRepository->expects($this->once())
            ->method('insert')
            ->willReturn(1);
        $mockAttachmentRepository->expects($this->once())
            ->method('findByParentId')
            ->with('report', 1)
            ->willReturn([
                ['client_file_name' => 'default-template.docx']
            ]);

        $vars = [
            'configuration' => [],
            'project' => [],
            'serviceProvider' => [],
            'date' => date('Y-m-d'),
            'revisions' => [],
            'lastRevisionName' => null,
            'markdownParser' => [],
            'client' => null,
            'assets' => [],
            'tasks' => [],
            'findings' => [
                'list' => [],
                'stats' => []
            ],
            'users' => [],
        ];

        $mockReportDataCollector = $this->createMock(ReportDataCollector::class);
        $mockReportDataCollector->expects($this->once())
            ->method('collectForProject')
            ->willReturn($vars);

        $controller = new CreateReportController($mockAttachmentFilePath, $mockProjectRepository, $mockReportRepository, $mockAttachmentRepository, $mockReportDataCollector);
        $controller->setLogger($this->createMock(Logger::class));
        $response = $controller($mockRequest);

        $expectedAttachmentIds = [1];

        $this->assertEquals($expectedAttachmentIds, $response);
    }
}
