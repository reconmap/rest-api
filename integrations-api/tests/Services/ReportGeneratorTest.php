<?php declare(strict_types=1);

namespace Reconmap\Services;

use PHPUnit\Framework\TestCase;
use Reconmap\Services\Filesystem\AttachmentFilePath;
use Reconmap\Services\Reporting\ReportDataCollector;
use Reconmap\Services\Reporting\ReportGenerator;

class ReportGeneratorTest extends TestCase
{
    public function testHappyPath()
    {
        $mockReportDataCollector = $this->createMock(ReportDataCollector::class);
        $mockReportDataCollector->expects($this->once())
            ->method('collectForProject')
            ->with(1)
            ->willReturn([]);

        $mockAttachmentFilePath = $this->createMock(AttachmentFilePath::class);
        $mockAttachmentFilePath->expects($this->once())
            ->method('generateBasePath')
            ->willReturn('data/attachments');

        $subject = new ReportGenerator($mockReportDataCollector, $mockAttachmentFilePath);

        $report = $subject->generate(1);

        $this->assertStringContainsString('<title>Penetration Test Report</title>', $report);
    }
}
