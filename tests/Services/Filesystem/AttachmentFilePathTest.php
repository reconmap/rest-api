<?php declare(strict_types=1);

namespace Reconmap\Services\Filesystem;

use PHPUnit\Framework\TestCase;
use Reconmap\Services\ApplicationConfig;

class AttachmentFilePathTest extends TestCase
{
    private $mockApplicationConfig;
    private AttachmentFilePath $subject;

    public function setUp(): void
    {
        $this->mockApplicationConfig = $this->createMock(ApplicationConfig::class);
        $this->mockApplicationConfig->expects($this->once())
            ->method('getAppDir')
            ->willReturn('/app/reconmap');

        $this->subject = new AttachmentFilePath($this->mockApplicationConfig);
    }

    public function testGenerateBasePath()
    {
        $this->assertEquals('/app/reconmap/data/attachments/', $this->subject->generateBasePath());
    }

    public function testGenerateFilePath()
    {
        $this->assertEquals('/app/reconmap/data/attachments/test.file', $this->subject->generateFilePath('test.file'));
    }

    public function testGenerateFilePathFromAttachment()
    {
        $this->assertEquals('/app/reconmap/data/attachments/document.txt', $this->subject->generateFilePathFromAttachment(['file_name' => 'document.txt']));
    }
}
