<?php declare(strict_types=1);

namespace Reconmap\Services;

use PHPUnit\Framework\TestCase;
use Reconmap\Services\Reporting\ReportDataCollector;
use Reconmap\Services\Reporting\ReportGenerator;

class ReportGeneratorTest extends TestCase
{
    public function testHappyPath()
    {
        $mockTemplateEngine = $this->createMock(TemplateEngine::class);

        $mockReportDataCollector = $this->createMock(ReportDataCollector::class);
        $mockReportDataCollector->expects($this->once())
            ->method('collectForProject')
            ->with(1)
            ->willReturn([

            ]);

        $subject = new ReportGenerator($mockReportDataCollector, $mockTemplateEngine);

        $report = $subject->generate(1);

        $expectedReport = [
            'body' => '',
        ];
        $this->assertEquals($expectedReport, $report);
    }
}
