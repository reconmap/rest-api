<?php declare(strict_types=1);

namespace Reconmap\Services\Reporting;

use Reconmap\Services\Filesystem\AttachmentFilePath;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

readonly class ReportGenerator
{
    public function __construct(
        private ReportDataCollector $reportDataCollector,
        private AttachmentFilePath  $attachmentFilePathService)
    {
    }

    public function generate(int $projectId): string
    {
        $vars = $this->reportDataCollector->collectForProject($projectId);


        $basePath = $this->attachmentFilePathService->generateBasePath();
        $filesystemLoader = new FileSystemLoader($basePath);
        $twig = new Environment($filesystemLoader, ['strict_variables' => false]);
        return $twig->render('default-report-template.html', $vars);
    }
}
