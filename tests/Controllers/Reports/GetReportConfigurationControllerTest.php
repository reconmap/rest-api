<?php declare(strict_types=1);

namespace Reconmap\Controllers\Reports;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Models\ReportConfiguration;
use Reconmap\Repositories\ReportConfigurationRepository;

class GetReportConfigurationControllerTest extends TestCase
{
    public function testHappyPath()
    {
        $reportConfiguration = new ReportConfiguration();

        $mockRequest = $this->createMock(ServerRequestInterface::class);

        $mockRepository = $this->createMock(ReportConfigurationRepository::class);
        $mockRepository->expects($this->once())
            ->method('findByProjectId')
            ->with(6)
            ->willReturn($reportConfiguration);

        $args = ['projectId' => 6];

        $controller = new GetReportConfigurationController($mockRepository);
        $response = $controller($mockRequest, $args);

        $this->assertEquals($reportConfiguration, $response);
    }
}
