<?php declare(strict_types=1);

namespace Reconmap\Controllers\Reports;

use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Repositories\AttachmentRepository;
use Reconmap\Services\AttachmentFilePath;
use Reconmap\Services\EmailService;

class SendReportControllerTest extends TestCase
{
    public function testHappyPath()
    {
        $mockAttachment = [];

        $mockAttachmentFilePath = $this->createMock(AttachmentFilePath::class);
        $mockAttachmentFilePath->expects($this->once())
            ->method('generateFilePathFromAttachment')
            ->with($mockAttachment);

        $mockAttachmentRepository = $this->createMock(AttachmentRepository::class);
        $mockAttachmentRepository->expects($this->once())
            ->method('findByParentId')
            ->with('report', 14, 'application/pdf')
            ->willReturn([$mockAttachment]);

        $mockEmailService = $this->createMock(EmailService::class);
        $mockEmailService->expects($this->once())
            ->method('queueEmail')
            ->with('Your report is ready', ['foo@bar.'], 'Find attached the report', '');

        $mockServerRequest = $this->createMock(ServerRequestInterface::class);
        $mockServerRequest->expects($this->once())
            ->method('getBody')
            ->willReturn('{"subject": "Your report is ready", "report_id": 14, "body": "Find attached the report", "recipients": "foo@bar."}');

        $controller = new SendReportController($mockAttachmentFilePath, $mockAttachmentRepository, $mockEmailService);
        $controller->setLogger($this->createMock(Logger::class));
        $controller($mockServerRequest);
    }
}
