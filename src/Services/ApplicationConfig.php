<?php declare(strict_types=1);

namespace Reconmap\Services;

use Noodlehaus\Config;

class ApplicationConfig extends Config
{
    public function getSettings(string $name): array
    {
        return $this[$name];
    }

    public function setAppDir(string $appDir): void
    {
        $this['appDir'] = $appDir;
    }

    public function getAppDir(): string
    {
        return $this['appDir'];
    }
}
