<?php declare(strict_types=1);

namespace Reconmap\Controllers\Reports;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Repositories\ReportRepository;

class GetReportsControllerTest extends TestCase
{
    public function testHappyPath()
    {
        $mockReports = [['title' => 'foo']];

        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->once())
            ->method('getQueryParams')
            ->willReturn(['projectId' => 4]);

        $mockRepository = $this->createMock(ReportRepository::class);
        $mockRepository->expects($this->once())
            ->method('findByProjectId')
            ->with(4)
            ->willReturn($mockReports);

        $controller = new GetReportsController($mockRepository);
        $response = $controller($mockRequest);

        $this->assertEquals($mockReports, $response);
    }
}
