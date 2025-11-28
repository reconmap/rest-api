<?php declare(strict_types=1);

namespace Reconmap\Services\Reporting\Renderers;

use Psr\Log\LoggerInterface;
use Reconmap\Models\Attachment;
use Reconmap\Models\Report;
use Reconmap\Repositories\AttachmentRepository;
use Reconmap\Services\Filesystem\AttachmentFilePath;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class PlainTextReportRenderer
{
    public function __construct(private readonly LoggerInterface      $logger,
                                private readonly AttachmentFilePath   $attachmentFilePathService,
                                private readonly AttachmentRepository $attachmentRepository,)
    {
    }

    public function render(array $project, Report $report, array $reportTemplateAttachment, array $vars): int
    {
        $templateFilePath = $this->attachmentFilePathService->generateFilePathFromAttachment($reportTemplateAttachment);

        $ext = pathinfo($reportTemplateAttachment['client_file_name'], PATHINFO_EXTENSION);

        $fileName = uniqid(gethostname());
        $basePath = $this->attachmentFilePathService->generateBasePath();
        $filePath = $basePath . $fileName;

        $filesystemLoader = new FileSystemLoader($basePath);
        $twig = new Environment($filesystemLoader, ['strict_variables' => true]);
        $evaluatedContent = $twig->render(basename($templateFilePath), $vars);
        file_put_contents($filePath, $evaluatedContent);;

        $projectName = str_replace(' ', '_', strtolower($project['name']));
        $clientFileName = "reconmap-$projectName-v{$report->versionName}." . $ext;

        $attachment = new Attachment();
        $attachment->parent_type = 'report';
        $attachment->parent_id = $report->id;;
        $attachment->submitter_uid = $report->generatedByUid;

        $attachment->file_name = $fileName;
        $attachment->file_mimetype = 'text/plain; charset=utf-8';
        $attachment->file_hash = hash_file('md5', $filePath);
        $attachment->file_size = filesize($filePath);
        $attachment->client_file_name = $clientFileName;

        return $this->attachmentRepository->insert($attachment);
    }
}
