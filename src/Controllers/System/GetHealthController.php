<?php declare(strict_types=1);

namespace Reconmap\Controllers\System;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Services\Filesystem\ApplicationLogFilePath;
use Reconmap\Services\Filesystem\AttachmentFilePath;
use Reconmap\Services\Filesystem\DirectoryChecker;

class GetHealthController extends Controller
{
    public function __construct(
        private readonly AttachmentFilePath $attachmentFilePath,
        private readonly ApplicationLogFilePath $applicationLogFilePath,
        private readonly DirectoryChecker $directoryChecker,
        private readonly \mysqli $dbConnection
    )
    {
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        $attachmentBasePath = $this->attachmentFilePath->generateBasePath();
        $logsPath = $this->applicationLogFilePath->getDirectory();

        return [
            'attachmentsDirectory' => $this->directoryChecker->checkDirectoryIsWriteable($attachmentBasePath),
            'logsDirectory' => $this->directoryChecker->checkDirectoryIsWriteable($logsPath),
            'dbConnection' => [
                'ping' => $this->dbConnection->ping()
            ]
        ];
    }
}
