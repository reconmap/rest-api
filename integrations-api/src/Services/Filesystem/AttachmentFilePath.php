<?php declare(strict_types=1);

namespace Reconmap\Services\Filesystem;

use Reconmap\Services\ApplicationConfig;

readonly class AttachmentFilePath
{
    public function __construct(private ApplicationConfig $config)
    {
    }

    public function generateFileName(): string
    {
        return uniqid(gethostname());
    }

    public function generateBasePath(): string
    {
        return $this->config->getAppDir() . '/data/attachments/';
    }

    public function generateFilePath(string $fileName): string
    {
        return $this->generateBasePath() . $fileName;
    }

    public function generateFilePathFromAttachment(array $attachment): string
    {
        return $this->generateFilePath($attachment['file_name']);
    }
}
