<?php declare(strict_types=1);

namespace Reconmap\Services;

class ConfigLoader
{
    public function loadFromFile(string $path): Config
    {
        $settings = json_decode(file_get_contents($path), true);
        return new Config($settings);
    }
}
