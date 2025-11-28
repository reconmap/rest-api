<?php declare(strict_types=1);

namespace Reconmap\Controllers\Reports;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Repositories\ReportRepository;

class GetReportTemplatesControllerTest extends TestCase
{
    public function testHappyPath()
    {
        $mockReportRepository = $this->createMock(ReportRepository::class);
        $mockReportRepository->expects($this->once())
            ->method('findTemplates')
            ->willReturn([]);

        $mockRequest = $this->createMock(ServerRequestInterface::class);

        $controller = new GetReportTemplatesController($mockReportRepository);
        $templates = $controller($mockRequest);
        $this->assertEquals([], $templates);
    }
}
