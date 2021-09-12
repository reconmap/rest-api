<?php declare(strict_types=1);

namespace Reconmap\Services\Filesystem;

class DirectoryChecker
{
    public function checkDirectoryIsWriteable(string $directoryPath): array
    {
        return [
            'location' => $directoryPath,
            'exists' => is_dir($directoryPath),
            'writeable' => is_writeable($directoryPath)
        ];
    }
}
