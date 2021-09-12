<?php declare(strict_types=1);

namespace Reconmap\Services\Filesystem;

use Reconmap\Services\ApplicationConfig;

class ApplicationLogFilePath
{
    public function __construct(private ApplicationConfig $config)
    {

    }

    public function getDirectory(): string
    {
        return $this->config->getAppDir() . '/logs';
    }
}
